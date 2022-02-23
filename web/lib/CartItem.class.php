<?php

class CartItem {
    private $id;

    private $productId;

    private $variantId;

    private $name;

    private $variantName;

    private $currency;

    private $unitPrice = 0;

    private $totalPrice = 0;

    private $vatKey;

    private $vat;

    private $quantity = 1;

    private $quantityUnit;

    public function __construct(int $id, int $productId, int $variantId)
    {
        $this->id = $id;

        $this->productId = $productId;

        $this->variantId = $variantId;
    }

    public function setName(string $name, string $variantName = ''):self
    {
        $this->name = $name;

        $this->variantName = $variantName;

        return $this;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function getVariantName():string
    {
        return $this->variantName;
    }

    public function setPrice(float $unitPrice, string $currency, int $quantity = 1, string $unit = 'db'):self
    {
        $this->unitPrice = $unitPrice;

        $this->currency = $currency;

        $this->quantity = $quantity;

        $this->quantityUnit = $unit;

        $this->totalPrice = $this->unitPrice * $this->quantity;

        return $this;
    }

    public function getUnitPrice():float
    {
        return $this->unitPrice;
    }

    public function getCurrency():string
    {
        return $this->currency;
    }

    public function setVat(string $vatKey, float $vat):self
    {
        $this->vatKey = $vatKey;

        $this->vat = $vat;

        return $this;
    }

    public function getVatKey():string
    {
        return $this->vatKey;
    }

    public function getVat():float
    {
        return $this->vat;
    }

    public function setQuantity(int $quantity = 1, string $unit = 'db'):self
    {
        $this->quantity = $quantity;

        $this->quantityUnit = $unit;

        return $this;
    }

    public function getQuantity():int
    {
        return $this->quantity;
    }

    public function getQuantityUnit():string
    {
        return $this->quantityUnit;
    }

}