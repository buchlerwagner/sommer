<?php

class Transaction {
    public $id;
    public $transactionId;
    public $providerId;
    public $created;
    public $authCode;
    public $amount;
    public $currency;
    public $message;
    public $cartId;
    public $cartKey;

    private $status;

    public function __construct(array $transaction = [])
    {
        $this->setStatus(enumPaymentStatus::Pending());

        if($transaction){
            foreach($transaction AS $key => $value){
                if(property_exists($this, $key)){
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function setStatus(enumPaymentStatus $status):self
    {
        $this->status = $status->getValue();
        return $this;
    }

    public function getStatus(){
        return $this->status;
    }
}