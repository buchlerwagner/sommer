<?php

class InvoiceProviderSettings {
    public $id;
    public $className;
    public $userName;
    public $apiKey;

    private $isTest = 0;
    private $password = '';

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

}