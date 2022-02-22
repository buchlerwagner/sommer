<?php
abstract class InvoiceProvider extends ancestor {
    /**
     * @var InvoiceProviderSettings|null
     */
    protected $settings = null;

    protected $paymentMethod;

    protected $orderNumber;

    protected $issueDate;

    protected $dueDate;

    protected $fulfillmentDate;

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

    public function __construct(InvoiceProviderSettings $settings)
    {
        $this->settings = $settings;
    }

    public function setBuyer(InvoiceBuyer $buyer):self
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function addItem(InvoiceItem $item):self
    {
        $this->items[] = $item;
        return $this;
    }
}
