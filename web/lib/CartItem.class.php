<?php

class CartItem {
    public $id;

    public $productId;

    public $variantId;

    private $name;

    private $variantName;

    private $unitPrice = 0;

    private $totalNetPrice = 0;

    private $totalGrossPrice = 0;

    private $vatKey;

    private $vatAmount = 0;

    private $vat = 1;

    private $quantity = 1;

    private $quantityUnit;

    private $isDiscounted = false;

    public function __construct(int $id = 0, int $productId = 0, int $variantId = 0)
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

    public function setUnitPrice(float $unitPrice):self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getUnitPrice():float
    {
        return $this->unitPrice;
    }

    public function getNetPrice():float
    {
        return $this->totalNetPrice;
    }

    public function getGrossPrice():float
    {
        return $this->totalGrossPrice;
    }

    public function getVatAmount():float
    {
        return $this->vatAmount;
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

    public function setDiscounted(bool $isDiscounted):self
    {
        $this->isDiscounted = $isDiscounted;
        return $this;
    }

    public function isDiscounted():bool
    {
        return $this->isDiscounted;
    }

    final public function summarize(int $priceBase):self
    {
        if($priceBase == Cart::PRICE_BASE_GROSS){
            $this->totalGrossPrice = $this->unitPrice * $this->quantity;
            $this->unitPrice = round($this->unitPrice / (1 + $this->vat), 1);
            $this->totalNetPrice = $this->unitPrice * $this->quantity;
            $this->vatAmount = $this->totalGrossPrice - $this->totalNetPrice;

        }else{
            $this->totalNetPrice = $this->unitPrice * $this->quantity;
            $this->vatAmount = round($this->vat * $this->quantity);

            $this->totalGrossPrice = $this->totalNetPrice + $this->vatAmount;
        }

        return $this;
    }

}