<?php

class CartItem {
    private $id;

    private $productId;

    private $variantId;

    private $name;

    private $variantName;

    private $currency;

    private $unitPrice;

    private $totalPrice;

    private $vatKey;

    private $vat;

    private $quantity;

    private $quantityUnit;

    public function __construct(int $id, int $productId, int $variantId)
    {
        $this->id = $id;

        $this->productId = $productId;

        $this->variantId = $variantId;
    }

}