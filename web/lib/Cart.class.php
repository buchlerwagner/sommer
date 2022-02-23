<?php

class Cart {
    private $id;

    private $orderStatus;

    private $orderDate;

    private $orderNumber;

    private $currency = 0;

    private $total = 0;

    private $discount = 0;

    private $packagingFee = 0;

    private $shippingFee = 0;

    private $paymentFee = 0;

    private $isPaid = false;

    private $customer = [];

    private $shippingMode = [];

    private $paymentMode = [];

    private $invoiceProviderId = 0;

    private $items = [];

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function setOrderStatus(?string $status):self
    {
        $this->orderStatus = $status;
        return $this;
    }

    public function getOrderStatus():string
    {
        return $this->orderStatus;
    }

    public function setOrderDate(?string $date):self
    {
        $this->orderDate = $date;
        return $this;
    }

    public function getOrderDate():string
    {
        return $this->orderDate;
    }

    public function setOrderNumber(?string $orderNumber):self
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getOrderNumber():string
    {
        return $this->orderNumber;
    }

    public function setCurrency(string $currency):self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency():float
    {
        return $this->currency;
    }

    public function setTotal(float $total):self
    {
        $this->total = $total;
        return $this;
    }

    public function getTotal():float
    {
        return $this->total;
    }

    public function setDiscount(float $discount):self
    {
        $this->discount = $discount;
        return $this;
    }

    public function getDiscount():float
    {
        return $this->discount;
    }

    public function setPackagingFee(float $fee):self
    {
        $this->packagingFee = $fee;

        return $this;
    }

    public function getPackagingFee():float
    {
        return $this->packagingFee;
    }

    public function setPaymentFee(float $fee):self
    {
        $this->paymentFee = $fee;

        return $this;
    }

    public function getPaymentFee():float
    {
        return $this->paymentFee;
    }


    public function setShippingFee(?float $fee):self
    {
        $this->shippingFee = $fee;

        return $this;
    }

    public function getShippingFee():float
    {
        return $this->shippingFee;
    }

    public function setPaid(bool $paid):self
    {
        $this->isPaid = $paid;
        return $this;
    }

    public function isPaid():bool
    {
        return $this->isPaid;
    }

    public function setCustomer(?array $customer):self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getCustomer():array
    {
        return $this->customer;
    }

    public function setShippingMode(?array $shippingMode):self
    {
        $this->shippingMode = $shippingMode;
        return $this;
    }

    public function getShippingMode():array
    {
        return $this->shippingMode;
    }

    public function setPaymentMode(?array $paymentMode):self
    {
        $this->paymentMode = $paymentMode;
        return $this;
    }

    public function getPaymentMode():array
    {
        return $this->paymentMode;
    }

    public function addItem(CartItem $item):self
    {
        $this->items[] = $item;
        return $this;
    }

    public function getItems():array
    {
        return $this->items;
    }

    public function setInvoiceProvider(int $id):self
    {
        $this->invoiceProviderId = $id;
        return $this;
    }

    public function getInvoiceProvider():int
    {
        return $this->invoiceProviderId;
    }
}