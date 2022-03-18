<?php

class InvoiceProviderSettings {
    public $id;
    public $className;
    public $userName;
    public $apiKey;

    private $isTest = 0;
    private $isManual = 1;
    private $password = '';

    public function __construct(array $settings)
    {
        $this->id = null;

        if($settings){
            foreach($settings AS $key => $value){
                if(property_exists($this, $key)){
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function isManual():bool
    {
        return ($this->isManual);
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