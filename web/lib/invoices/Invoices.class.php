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
     * @var Cart
     */
    private $cart = null;

    private $invoiceNumber;

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

    public function init(?Cart $cart = null):self
    {
        if($cart) {
            $this->cart = $cart;
            $this->providerId = $cart->getInvoiceProvider();
        }

        if($this->settings = $this->loadSettings($this->providerId)){
            $this->provider = $this->owner->addByClassName($this->settings->className, false, [
                $this->settings
            ]);
        }

        return $this;
    }

    public function getTaxPayer($taxNumber)
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

    private function setBuyer():self
    {
        $this->buyer = new InvoiceBuyer();
        /*
        $buyer->setEmail($cart['us_email']);

        if($cart['us_email']) {
            $buyer->setSendEmail(true);
        }else{
            $buyer->setSendEmail(false);
        }

            $buyer->setName($cart['us_invoice_name']);

            $buyer->setVatNumber($cart['us_vat']);
            $buyer->setCountry($cart['us_invoice_country']);
            $buyer->setZipCode($cart['us_invoice_zip']);
            $buyer->setCity($cart['us_invoice_city']);
            $buyer->setAddress($cart['us_invoice_address']);

            $buyer->setName($cart['us_lastname'] . ' ' . $cart['us_firstname']);
            $buyer->setCountry($cart['us_country']);
            $buyer->setZipCode($cart['us_zip']);
            $buyer->setCity($cart['us_city']);
            $buyer->setAddress($cart['us_address']);


        if($cart['us_phone']){
            $buyer->setPhone($cart['us_phone']);
        }

        $this->buyer = $buyer;
        */
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
