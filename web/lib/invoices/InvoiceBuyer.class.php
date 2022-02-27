<?php

class InvoiceBuyer {
    private $id;
    private $name;
    private $country;
    private $zipCode;
    private $city;
    private $address;
    private $vatNumber;
    private $email;
    private $phone;
    private $comment;

    private $sendEmail = true;

    public function setId(int $id):self
    {
        $this->id = $id;
        return $this;
    }

    public function getId():int
    {
        return $this->id;
    }

    public function setName(string $name):self
    {
        $this->name = $name;
        return $this;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function setCountry(string $country):self
    {
        $this->country = $country;
        return $this;
    }

    public function getCountry():string
    {
        return $this->country;
    }

    public function setZipCode(string $zipCode):self
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getZipCode():string
    {
        return $this->zipCode;
    }

    public function setCity(string $city):self
    {
        $this->city = $city;
        return $this;
    }

    public function getCity():string
    {
        return $this->city;
    }

    public function setAddress(string $address):self
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress():string
    {
        return $this->address;
    }

    public function setVatNumber(?string $vatNumber):self
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    public function getVatNumber():?string
    {
        return $this->vatNumber;
    }

    public function setEmail(string $email):self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail():string
    {
        return $this->email;
    }

    public function setPhone(string $phone):self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone():string
    {
        return $this->phone;
    }

    public function setComment(string $comment):self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment():string
    {
        return $this->comment;
    }

    public function setSendEmail(bool $send):self
    {
        $this->sendEmail = $send;
        return $this;
    }

    public function isSendEmail():bool
    {
        return $this->sendEmail;
    }
}