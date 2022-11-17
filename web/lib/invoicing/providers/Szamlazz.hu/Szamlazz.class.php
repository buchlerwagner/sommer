<?php
require __DIR__ . '/autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\SzamlaAgentException;
use \SzamlaAgent\SzamlaAgentUtil;
use \SzamlaAgent\CreditNote\InvoiceCreditNote;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\TaxPayer;
use \SzamlaAgent\Document\Invoice\Invoice;
use \SzamlaAgent\Item\InvoiceItem;

class Szamlazz extends InvoiceProvider {
    const PROVIDER_NAME = 'Számlázz.hu';

    /**
     * @var $agent SzamlaAgentAPI
     */
    private $agent;

    public static function isAvailable(): bool {
        return true;
    }

    public static function getName(): string {
        return self::PROVIDER_NAME;
    }

    protected function init(): void {
        if($this->settings->apiKey){
            try {
                SzamlaAgentUtil::setBasePath(DIR_LOG . 'szamlazz.hu');
                $this->agent = SzamlaAgentAPI::create($this->settings->apiKey, true);


            } catch (SzamlaAgentException $e) {
                $this->agent->logError($e->getMessage());
            } catch (\Exception $e) {
                $this->agent->logError($e->getMessage());
            }
        }
    }

    public function getTaxPayer(string $taxNumber): ?InvoiceBuyer {
        $result = $this->agent->getTaxPayer($taxNumber);
        if($result->isSuccess()) {
            return $this->parseTaxPayerXML($result->getTaxPayerData());
        }

        return null;
    }

    public function createInvoice():?string
    {
        // Új e-számla létrehozása alapértelmezett adatokkal
        $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);

        // Számla fejléce
        $header = $invoice->getHeader();

        $header->setPaymentMethod($this->paymentMethod);

        // Számla pénzneme
        $header->setCurrency($this->currency);

        // Számla nyelve
        $header->setLanguage(strtolower($this->language));

        // Számla kifizetettség (fizetve)
        $header->setPaid($this->isPaid);

        // Számla teljesítés dátuma
        $header->setFulfillment($this->fulfillmentDate);

        // Számla fizetési határideje
        $header->setPaymentDue($this->dueDate);

        // Számla kiállítás dátuma
        $header->setIssueDate($this->issueDate);

        if($this->settings->prefix) {
            $header->setPrefix($this->settings->prefix);
        }

        // Vevő létrehozása (név, irányítószám, település, cím)
        $buyer = new Buyer($this->buyer->getName(), $this->buyer->getZipCode(), $this->buyer->getCity(), $this->buyer->getAddress());
        // Vevő telefonszáma
        if($this->buyer->getPhone()) {
            $buyer->setPhone($this->buyer->getPhone());
        }

        if($this->buyer->getVatNumber()) {
            // Vevő adószáma
            $buyer->setTaxNumber($this->buyer->getVatNumber());

            // Vevő adóalanyisága (van magyar adószáma)
            $buyer->setTaxPayer(TaxPayer::TAXPAYER_HAS_TAXNUMBER);
        }else{
            $buyer->setTaxPayer(TaxPayer::TAXPAYER_NO_TAXNUMBER);
        }

        if($this->buyer->getEmail()) {
            // Ha egyedi e-mail üzenetet állítunk be a vevő számára (lásd fentebb az Eladónál), akkor az e-mail kiküldéséhez az alábbi 2 mező beállítása is szükséges:
            $buyer->setEmail($this->buyer->getEmail());
            $buyer->setSendEmail($this->buyer->isSendEmail());
        }

        // Vevő hozzáadása a számlához
        $invoice->setBuyer($buyer);

        /**
         * @var $item InvoiceItem
         */

        foreach($this->items AS $item){
            // Számla tétel összeállítása egyedi adatokkal
            $invoiceItem = new InvoiceItem(
                $item->getName(),
                $item->getNetUnitPrice(),
                $item->getQuantity(),
                $item->getQuantityUnit(),
                $item->getVat()
            );

            if($comment = $item->getComment()) {
                $invoiceItem->setComment($comment);
            }

            // Tétel nettó értéke
            $invoiceItem->setNetPrice($item->getNetPrice());
            // Tétel ÁFA értéke
            $invoiceItem->setVatAmount($item->getVatAmount());
            // Tétel bruttó értéke
            $invoiceItem->setGrossAmount($item->getGrossPrice());

            // Tétel hozzáadása a számlához
            $invoice->addItem($invoiceItem);
        }

        // Számla elkészítése
        $result = $this->agent->generateInvoice($invoice);

        // Agent válasz sikerességének ellenőrzése
        if ($result->isSuccess()) {
            $this->setInvoiceNumber($result->getDocumentNumber());
        }

        return $this->getInvoiceNumber();
    }

    public function setPaymentMethod(int $method):InvoiceProvider
    {
        switch($method){
            case PAYMENT_TYPE_CASH:
                $this->paymentMethod = Invoice::PAYMENT_METHOD_CASH;
                break;
            case PAYMENT_TYPE_MONEY_TRANSFER:
                $this->paymentMethod = Invoice::PAYMENT_METHOD_TRANSFER;
                break;
            case PAYMENT_TYPE_CARD:
                $this->paymentMethod = Invoice::PAYMENT_METHOD_BANKCARD;
                break;
        }

        return $this;
    }

    public function downloadInvoice():string
    {
        $path = SzamlaAgentUtil::getBasePath() . ltrim(SzamlaAgentAPI::PDF_FILE_SAVE_PATH, '.') . '/';
        $fileName = $path . $this->invoiceNumber . '.pdf';

        if(!file_exists($fileName)) {
            $result = $this->agent->getInvoicePdf($this->invoiceNumber);

            // Agent válasz sikerességének ellenőrzése
            if ($result->isSuccess()) {
                //$result->downloadPdf();
                return $result->getPdfFileName(true);
            } else {
                return '';
            }
        }else{
            return $fileName;
        }
    }

    public function setInvoicePaid(float $amount):bool
    {
        // Új számla létrehozása
        $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);
        // Számla fejléce
        $header = $invoice->getHeader();
        // Annak a számlának a számlaszáma, amelyikhez a jóváírást szeretnénk rögzíteni
        $header->setInvoiceNumber($this->invoiceNumber);
        // Fejléc hozzáadása a számlához
        $invoice->setHeader($header);

        // Hozzáadjuk a jóváírás összegét (false esetén felülírjuk a teljes összeget)
        $invoice->setAdditive(false);

        // Új jóváírás létrehozása (az összeget a számla devizanemében kell megadni)
        $creditNote = new InvoiceCreditNote(SzamlaAgentUtil::getTodayStr(), $amount, $this->paymentMethod);
        // Jóváírás hozzáadása a számlához
        $invoice->addCreditNote($creditNote);

        // Számla jóváírás elküldése
        $result = $this->agent->payInvoice($invoice);

        return $result->isSuccess();
    }

    private function parseTaxPayerXML(string $xml):InvoiceBuyer
    {
        $buyer = new InvoiceBuyer();

        $xml = str_replace('ns2:', '', $xml);
        $xml = str_replace('ns3:', '', $xml);

        preg_match('/<taxpayerValidity>(.*?)<\/taxpayerValidity>/is', $xml, $m);
        $buyer->setValid( (mb_convert_case($m[1], MB_CASE_LOWER) === 'true') );

        //preg_match('/<incorporation>.*?<incorporation>/is', $xml, $m);

        //preg_match('/<taxpayerName>(.*?)<taxpayerName>/is', $xml, $m);
        //$buyer->setName(htmlspecialchars_decode($m[1]));

        preg_match('/<taxpayerShortName>(.*?)<\/taxpayerShortName>/is', $xml, $m);
        $buyer->setName(htmlspecialchars_decode($m[1]));

        preg_match('/<countryCode>(.*?)<\/countryCode>/is', $xml, $m);
        $buyer->setCountry($m[1]);

        preg_match('/<postalCode>(.*?)<\/postalCode>/is', $xml, $m);
        $buyer->setZipCode($m[1]);

        preg_match('/<city>(.*?)<\/city>/is', $xml, $m);
        $buyer->setCity(mb_convert_case($m[1], MB_CASE_TITLE));

        preg_match('/<streetName>(.*?)<\/streetName>/is', $xml, $m);
        $address = mb_convert_case($m[1], MB_CASE_TITLE);

        preg_match('/<publicPlaceCategory>(.*?)<\/publicPlaceCategory>/is', $xml, $m);
        $address .= ' ' . mb_convert_case($m[1], MB_CASE_LOWER);

        preg_match('/<number>(.*?)<\/number>/is', $xml, $m);
        $address .= ' ' . $m[1];
        $buyer->setAddress(trim($address));

        preg_match('/<taxpayerId>(.*?)<\/taxpayerId>/is', $xml, $m);
        $vat = $m[1];
        preg_match('/<vatCode>(.*?)<\/vatCode>/is', $xml, $m);
        $vat .= '-' . $m[1];
        preg_match('/<countyCode>(.*?)<\/countyCode>/is', $xml, $m);
        $vat .= '-' . $m[1];
        $buyer->setVatNumber($vat);

        return $buyer;
    }

    public function getInvoice(): ?InvoiceProvider
    {
        $result = $this->agent->getInvoiceData($this->invoiceNumber);

        // Agent válasz sikerességének ellenőrzése
        if ($result->isSuccess()) {
            var_dump($result->getData());
        }

        return $this;
    }
}