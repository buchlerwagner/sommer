<?php

class InvoiceItem {
    private $id;
    private $name;
    private $quantity;
    private $quantityUnit;
    private $netUnitPrice;
    private $netPrice;
    private $grossPrice;
    private $vat;
    private $vatAmount;
    private $comment;

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

    public function setQuantity(string $quantity, string $quantityUnit):self
    {
        $this->quantity = $quantity;
        $this->quantityUnit = $quantityUnit;
        return $this;
    }

    public function getQuantity():string
    {
        return $this->quantity;
    }

    public function getQuantityUnit():string
    {
        return $this->quantityUnit;
    }

    public function setNetUnitPrice(float $price):self
    {
        $this->netUnitPrice = $price;
        return $this;
    }

    public function getNetUnitPrice():string
    {
        return $this->netUnitPrice;
    }

    public function setNetPrice(float $price):self
    {
        $this->netPrice = $price;
        return $this;
    }

    public function getNetPrice():string
    {
        return $this->netPrice;
    }

    public function setGrossPrice(float $price):self
    {
        $this->grossPrice = $price;
        return $this;
    }

    public function getGrossPrice():float
    {
        return $this->grossPrice;
    }

    public function setVat(string $vat):self
    {
        $this->vat = $vat;
        return $this;
    }

    public function getVat():string
    {
        return $this->vat;
    }

    public function setVatAmount(float $vat):self
    {
        $this->vatAmount = $vat;
        return $this;
    }

    public function getVatAmount():float
    {
        return $this->vatAmount;
    }

    public function setComment(?string $comment):self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment():?string
    {
        return $this->comment;
    }
}