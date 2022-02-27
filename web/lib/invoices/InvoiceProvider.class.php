<?php
abstract class InvoiceProvider {
    /**
     * @var InvoiceProviderSettings|null
     */
    protected $settings = null;

    protected $language;

    protected $invoiceNumber;

    protected $paymentMethod;

    protected $orderNumber;

    protected $issueDate;

    protected $dueDate;

    protected $currency;

    protected $fulfillmentDate;

    protected $isPaid;

    /**
     * @var $buyer InvoiceBuyer
     */
    protected $buyer;

    /**
     * @var $items InvoiceItem
     */
    protected $items = [];

    public abstract static function isAvailable():bool;

    public abstract static function getName():string;

    protected abstract function init(): void;

    public abstract function getTaxPayer(string $taxNumber):?array;

    public abstract function createInvoice();

    public abstract function downloadInvoice();

    public abstract function setInvoicePaid(float $amount):bool;

    public function __construct(InvoiceProviderSettings $settings, string $language = DEFAULT_LANGUAGE)
    {
        $this->settings = $settings;
        $this->language = $language;

        $this->init();
    }

    public function setInvoiceNumber(string $invoiceNumber):self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceNumber():?string
    {
        return $this->invoiceNumber;
    }

    public function setPaymentMethod(int $method):self
    {
        $this->paymentMethod = $method;
        return $this;
    }

    public function getPaymentMethod():int
    {
        return $this->paymentMethod;
    }

    public function setOrderNumber(string $orderNumber):self
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getOrderNumber():string
    {
        return $this->orderNumber;
    }

    public function setIssueDate(string $date):self
    {
        $this->issueDate = $date;
        return $this;
    }

    public function getIssueDate():string
    {
        return $this->issueDate;
    }

    public function setDueDate(string $date):self
    {
        $this->dueDate = $date;
        return $this;
    }

    public function getDueDate():string
    {
        return $this->dueDate;
    }

    public function setFulfillmentDate(string $date):self
    {
        $this->fulfillmentDate = $date;
        return $this;
    }

    public function getFulfillmentDate():string
    {
        return $this->fulfillmentDate;
    }

    public function setBuyer(InvoiceBuyer $buyer):self
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function getBuyer():InvoiceBuyer
    {
        return $this->buyer;
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

    public function setPaid(bool $isPaid):self
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    public function isPaid():bool
    {
        return $this->isPaid;
    }

    public function addItem(InvoiceItem $item):self
    {
        $this->items[] = $item;
        return $this;
    }

    public function getItems():array
    {
        return $this->items;
    }
}
