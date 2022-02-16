<?php

class PaymentProviderSettings {
    public $id;
    public $className;
    public $shopId;
    public $currency;
    public $urlFrontend;
    public $urlReturn;
    public $urlCallback;

    private $isTest = 0;
    private $password = '';
    private $privateKey = '';

    public function __construct(array $settings)
    {
        if($settings){
            foreach($settings AS $key => $value){
                if(property_exists($this, $key)){
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function isTestMode():bool
    {
        return ($this->isTest);
    }

    public function getPassword():string
    {
        if(!Empty($this->password)) {
            $pwd = unserialize($this->password);

            return deCryptString(SMTP_HASH_KEY, $pwd);
        }

        return '';
    }

    public function getPrivateKey():string
    {
        if(!Empty($filename)) {
            return DIR_PRIVATE_KEYS . $this->privateKey;
        }

        return '';
    }
}