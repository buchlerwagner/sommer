<?php

class Cart {
    const PRICE_BASE_NET = 1;
    const PRICE_BASE_GROSS = 2;

    public $id;

    private $priceBase;

    private $orderStatus;

    private $orderDate;

    private $shippingDate;

    private $orderNumber;

    private $invoiceNumber;

    private $invoiceFileName;

    private $currency;

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

    private $orderType = 0;

    /**
     * @var $items CartItem
     */
    private $items = [];

    public function __construct(int $id, int $priceBae = self::PRICE_BASE_NET)
    {
        $this->id = $id;

        $this->priceBase = $priceBae;
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

    public function setShippingDate(?string $date):self
    {
        $this->shippingDate = $date;
        return $this;
    }

    public function getShippingDate():string
    {
        return $this->shippingDate;
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

    public function getInvoiceNumber():string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber):self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function setCurrency(string $currency):self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency():string
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

    public function setPackagingFee(float $fee, string $vat = ''):self
    {
        $this->packagingFee = $fee;

        if($this->packagingFee){
            $item = new CartItem();
            $item->setName('Csomagolás'); // @todo: localize
            $item->setUnitPrice($this->packagingFee);
            $item->setVat($vat, $vat / 100);
            $item->setQuantity(1);

            $this->addItem($item);
        }

        return $this;
    }

    public function getPackagingFee():float
    {
        return $this->packagingFee;
    }

    public function setShipping(?array $shippingMode, ?float $shippingFee = 0):self
    {
        $this->shippingMode = $shippingMode;
        $this->shippingFee = $shippingFee;

        if($this->shippingFee){
            $item = new CartItem();
            $item->setName($this->shippingMode['name']);
            $item->setUnitPrice($this->shippingFee);
            $item->setVat($this->shippingMode['vat'], $this->shippingMode['vat'] / 100);
            $item->setQuantity(1);

            $this->addItem($item);
        }

        return $this;
    }

    public function getShippingMode():array
    {
        return $this->shippingMode;
    }

    public function getShippingFee():float
    {
        return $this->shippingFee;
    }

    public function setPayment(?array $paymentMode, ?float $paymentFee = 0):self
    {
        $this->paymentMode = $paymentMode;
        $this->paymentFee = $paymentFee;

        if($this->paymentFee){
            $item = new CartItem();
            $item->setName('Kezelési költség'); // @todo: localize
            $item->setUnitPrice($this->paymentFee);
            $item->setVat($this->paymentMode['vat'], $this->paymentMode['vat'] / 100);
            $item->setQuantity(1);

            $this->addItem($item);
        }

        return $this;
    }

    public function getPaymentMode():array
    {
        return $this->paymentMode;
    }

    public function getPaymentFee():float
    {
        return $this->paymentFee;
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

    public function addItem(CartItem $item):self
    {
        $this->items[] = $item->summarize($this->priceBase);
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

    public function setOrderType(int $orderType):self
    {
        $this->orderType = $orderType;
        return $this;
    }

    public function getOrderType():int
    {
        return $this->orderType;
    }

    public function setInvoiceFileName(?string $fileName):self
    {
        $this->invoiceFileName = $fileName;
        return $this;
    }

    public function getInvoiceFileName():string
    {
        return $this->invoiceFileName;
    }
}