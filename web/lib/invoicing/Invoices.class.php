<?php
class Invoices extends ancestor {
    const TRANSFER_DAYS = 8;

    /**
     * @var InvoiceProvider
     */
    private $provider = null;

    private $providerId = 0;

    /**
     * @var InvoiceProviderSettings
     */
    private $settings = [];

    /**
     * @var Cart
     */
    private $cart = null;

    private $allowInvoicing = true;

    public static function getProviders():array
    {
        $result = [];

        $directory = new RecursiveDirectoryIterator(__DIR__ . '/providers/');
        $flattened = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($flattened, '/(\.class)(\.php)$/');

        foreach($files as $file) {
            /**
             * @var $classname InvoiceProvider
             */
            $classname = substr(basename($file), 0, -10);
            if($classname::isAvailable()) {
                $result[$classname] = $classname::getName();
            }
        }

        return $result;
    }

    public function hasInvoiceProvider():bool
    {
        return (!Empty($this->loadDefaultSettings()));
    }

    public function overrideInvoicingAllowance():self
    {
        $this->allowInvoicing = true;
        return $this;
    }

    public function init(?Cart $cart = null):self
    {
        $this->providerId = 0;

        if($cart) {
            $this->cart = $cart;
            $this->providerId = $cart->getInvoiceProvider();
        }

        if($this->providerId){
            $this->settings = $this->loadSettings($this->providerId);
        }else{
            $this->settings = $this->loadDefaultSettings();
        }

        if($this->settings){
            $this->allowInvoicing = !$this->settings->isManual();

            if(class_exists($this->settings->className)) {
                $this->provider = new $this->settings->className($this->settings, $this->owner->language);
            }
        }

        return $this;
    }

    public function getTaxPayer(string $taxNumber):?InvoiceBuyer
    {
        if(!$this->settings){
            return null;
        }

        if(!$this->provider){
            throw new Exception('Invoice provider is not inited!');
        }

        list($taxNumber, ) = explode('-', $taxNumber);

        return $this->provider->getTaxPayer($taxNumber);
    }

    public function createInvoice($issueDate = '', $dueDate = '', $fulfillmentDate = ''):string
    {
        if(!$this->provider){
            throw new Exception('Invoice provider is not inited!');
        }

        if(!$this->allowInvoicing){
            return '';
        }

        if(!$this->cart){
            throw new Exception('Cart is empty!');
        }

        if(!$this->cart->getInvoiceNumber()) {
            $buyer = new InvoiceBuyer();

            $customer = $this->cart->getCustomer();

            $buyer->setName($customer['invoiceAddress']['name']);
            $buyer->setCountry($customer['invoiceAddress']['country']);
            $buyer->setZipCode($customer['invoiceAddress']['zip']);
            $buyer->setCity($customer['invoiceAddress']['city']);
            $buyer->setAddress($customer['invoiceAddress']['address']);

            if ($customer['invoiceAddress']['vatNumber']) {
                $buyer->setVatNumber($customer['invoiceAddress']['vatNumber']);
            }

            if ($customer['contactData']['email']) {
                $buyer->setEmail($customer['contactData']['email']);
                $buyer->setSendEmail(true);
            } else {
                $buyer->setSendEmail(false);
            }

            if ($customer['contactData']['phone']) {
                $buyer->setPhone($customer['contactData']['phone']);
            }

            $this->provider->setBuyer($buyer);

            $this->provider->setOrderNumber($this->cart->getOrderNumber());

            $today = date('Y-m-d');
            $issueDate = standardDate($issueDate ?: $this->cart->getOrderDate());
            if ($issueDate < $today) {
                $issueDate = $today;
            }

            $this->provider->setIssueDate($issueDate);
            $this->provider->setFulfillmentDate(($fulfillmentDate ? standardDate($fulfillmentDate) : $issueDate));

            $paymentMode = $this->cart->getPaymentMode();
            $this->provider->setPaymentMethod($paymentMode['type']);
            $this->provider->setCurrency($this->cart->getCurrency());
            $this->provider->setPaid($this->cart->isPaid());

            if ($paymentMode['type'] == PAYMENT_TYPE_MONEY_TRANSFER) {
                $this->provider->setDueDate(($dueDate ? standardDate($dueDate) : dateAddDays($issueDate, self::TRANSFER_DAYS)));

            } else {
                $this->provider->setDueDate(($dueDate ? standardDate($dueDate) : $issueDate));
            }

            /**
             * @var $item CartItem
             */

            $cartItems = $this->cart->getItems();
            if ($cartItems) {
                foreach ($cartItems as $item) {
                    $invoiceItem = new InvoiceItem();

                    $invoiceItem->setId($item->id);
                    $invoiceItem->setName($item->getName());

                    if ($item->getVariantName()) {
                        $invoiceItem->setComment($item->getVariantName());
                    }

                    $invoiceItem->setVat($item->getVatKey());
                    $invoiceItem->setQuantity($item->getQuantity(), $item->getQuantityUnit());

                    $invoiceItem->setNetUnitPrice($item->getUnitPrice());
                    $invoiceItem->setNetPrice($item->getNetPrice());
                    $invoiceItem->setGrossPrice($item->getGrossPrice());
                    $invoiceItem->setVatAmount($item->getVatAmount());

                    $this->provider->addItem($invoiceItem);
                }
            }

            if ($invoiceNumber = $this->provider->createInvoice()) {
                $this->saveInvoiceNumber($invoiceNumber);
                $this->downloadInvoice($invoiceNumber);
            }
        }

        return $this->provider->getInvoiceNumber();
    }

    public function setPaid():bool
    {
        if(!$this->provider){
            throw new Exception('Invoice provider is not inited!');
        }

        if(!$this->cart){
            throw new Exception('Cart is empty!');
        }

        if(!$this->cart->getInvoiceNumber()){
            throw new Exception('Missing invoice number!');
        }

        if(!$this->cart->isPaid()) {
            $this->provider->setInvoiceNumber($this->cart->getInvoiceNumber());
            $paymentMode = $this->cart->getPaymentMode();

            $this->provider->setPaymentMethod($paymentMode['type']);

            return $this->provider->setInvoicePaid($this->cart->getTotal());
        }

        return false;
    }

    public function downloadInvoice(string $invoiceNumber = ''):void
    {
        if(!$this->provider){
            throw new Exception('Invoice provider is not inited!');
        }

        if(Empty($invoiceNumber)){
            $this->provider->setInvoiceNumber($this->cart->getInvoiceNumber());
        }else{
            $this->provider->setInvoiceNumber($invoiceNumber);
        }

        if($origFileName = $this->provider->downloadInvoice()){

            $savePath = DIR_UPLOAD . $this->owner->shopId . '/invoices/';
            $fileName = uuid::v4() . '.pdf';

            if(!is_dir($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
            }

            rename($origFileName, $savePath . $fileName);

            $this->saveInvoiceFile($fileName);
        }
    }

    public function getInvoice(string $invoiceNumber = ''):InvoiceProvider
    {
        if(!$this->provider){
            throw new Exception('Invoice provider is not inited!');
        }

        if(Empty($invoiceNumber)){
            $this->provider->setInvoiceNumber($this->cart->getInvoiceNumber());
        }else{
            $this->provider->setInvoiceNumber($invoiceNumber);
        }

        return $this->provider->getInvoice();
    }

    private function loadDefaultSettings():?InvoiceProviderSettings
    {
        static $settings = false;

        if(!$settings) {
            $settings = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'invoice_providers',
                    [
                        'iv_id AS id',
                        'iv_name AS name',
                        'iv_provider AS className',
                        'iv_user_name AS userName',
                        'iv_password AS password',
                        'iv_api_key AS apiKey',
                        'iv_test_mode AS isTest',
                        'iv_manual AS isManual',
                        'iv_prefix AS prefix',
                    ],
                    [
                        'iv_enabled' => 1,
                        'iv_shop_id' => $this->owner->shopId,
                    ]
                )
            );

            $this->providerId = ($settings['id'] ?: 0);
        }

        return ($settings ? new InvoiceProviderSettings($settings) : null);
    }

    private function loadSettings(int $providerId):?InvoiceProviderSettings
    {
        static $settings = false;

        if(!$settings) {
            $settings = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'invoice_providers',
                    [
                        'iv_id AS id',
                        'iv_name AS name',
                        'iv_provider AS className',
                        'iv_user_name AS userName',
                        'iv_password AS password',
                        'iv_api_key AS apiKey',
                        'iv_test_mode AS isTest',
                        'iv_manual AS isManual',
                        'iv_prefix AS prefix',
                    ],
                    [
                        'iv_id' => $providerId,
                        'iv_shop_id' => $this->owner->shopId,
                    ]
                )
            );
            if(!$settings){
                return $this->loadDefaultSettings();
            }
        }

        return ($settings ? new InvoiceProviderSettings($settings) : null);
    }

    private function saveInvoiceNumber(string $invoiceNumber):void
    {
        if($this->cart->id && $this->provider->getInvoiceNumber()){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart',
                    [
                        'cart_invoice_number' => $invoiceNumber,
                        'cart_invoice_provider' => $this->providerId
                    ],
                    [
                        'cart_id' => $this->cart->id,
                        'cart_shop_id' => $this->owner->shopId,
                    ]
                )
            );
        }
    }

    private function saveInvoiceFile(string $fileName):void
    {
        if($this->cart->id && $fileName){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart',
                    [
                        'cart_invoice_filename' => $fileName,
                    ],
                    [
                        'cart_id' => $this->cart->id,
                        'cart_shop_id' => $this->owner->shopId,
                    ]
                )
            );
        }
    }
}
