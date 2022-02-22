<?php
require __DIR__ . '/autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\SzamlaAgentUtil;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\Document\Proforma;
use \SzamlaAgent\Document\Invoice\FinalInvoice;
use \SzamlaAgent\Item\InvoiceItem;
use \SzamlaAgent\Item\ProformaItem;
use \SzamlaAgent\CreditNote\InvoiceCreditNote;

class Szamlazz extends InvoiceProvider {
    private $agent;

    public static function isAvailable(): bool {
        return true;
    }

    public static function getName(): string {
        return 'Számlázz.hu';
    }
}