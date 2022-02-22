<?php
class Invoices extends ancestor {
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
     * @var InvoiceBuyer
     */
    private $buyer = null;

    /**
     * @var InvoiceItem
     */
    private $items = [];

    private $cartId;

    private $orderNumber;

    private $orderDate;

    private $currency;

    private $proformaNumber;

    private $invoiceNumber;

    private $invoiceType;

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

    public function init(int $cartId):self
    {
        $this->cartId = $cartId;
        $this->loadCart();

        if($this->settings = $this->loadSettings($this->providerId)){
            $this->provider = $this->owner->addByClassName($this->settings->className, false, [
                $this->settings
            ]);
        }

        return $this;
    }

    public function createProforma($paymentMethod = PAYMENT_TYPE_CARD, $issueDate = false, $dueDate = false, $fulfillmentDate = false)
    {

    }

    public function deleteProforma()
    {

    }

    public function createInvoice()
    {

    }

    public function setPaid()
    {

    }

    public function getInvoice()
    {

    }

    public function sendInvoice()
    {

    }

    private function loadSettings(int $providerId = 0):InvoiceProviderSettings
    {
        $where = [
            'iv_shop_id' => $this->owner->shopId,
        ];

        if($providerId){
            $where['iv_id'] = $providerId;
        }else{
            $where['iv_enabled'] = 1;
        }

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
                ],
                $where
            )
        );

        return new InvoiceProviderSettings(($settings ?: []));
    }

    private function loadCart():void
    {
        if($this->cartId){


            $cart = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'cart',
                    [],
                    [
                        'cart_id' => $this->cartId
                    ],
                    [
                        'users' => [
                            'on' => [
                                'us_id' => 'cart_us_id'
                            ]
                        ]
                    ]
                )
            );
            if ($cart) {
                $this->providerId = (int) $cart['cart_invoice_provider'];
                $this->orderNumber = $cart['cart_order_number'];
                $this->invoiceType = $cart['cart_invoice_type'];
                $this->proformaNumber = $cart['cart_proforma_number'];
                $this->invoiceNumber = $cart['cart_invoice_number'];

                if($cart['cart_us_id']) {
                    $buyer = new InvoiceBuyer();
                    $buyer->setId($cart['us_id']);
                    $buyer->setEmail($cart['us_email']);

                    if($cart['us_email']) {
                        $buyer->setSendEmail(true);
                    }else{
                        $buyer->setSendEmail(false);
                    }

                    if($this->invoiceType){
                        $buyer->setName($cart['us_invoice_name']);

                        if($this->invoiceType == 2) {
                            $buyer->setVatNumber($cart['us_vat']);
                        }

                        $buyer->setCountry($cart['us_invoice_country']);
                        $buyer->setZipCode($cart['us_invoice_zip']);
                        $buyer->setCity($cart['us_invoice_city']);
                        $buyer->setAddress($cart['us_invoice_address']);
                    }else {
                        $buyer->setName($cart['us_lastname'] . ' ' . $cart['us_firstname']);
                        $buyer->setCountry($cart['us_country']);
                        $buyer->setZipCode($cart['us_zip']);
                        $buyer->setCity($cart['us_city']);
                        $buyer->setAddress($cart['us_address']);
                    }

                    if($cart['us_phone']){
                        $buyer->setPhone($cart['us_phone']);
                    }

                    $this->setBuyer($buyer);
                }

                $this->getCartItems((bool) $cart['cart_local_consumption']);
            }
        }
    }


    private function setBuyer(InvoiceBuyer $buyer):self
    {
        $this->buyer = $buyer;
        return $this;
    }

    private function addItem(InvoiceItem $item):self
    {
        $this->items[] = $item;

        return $this;
    }

    private function updateCart(){

    }
}
