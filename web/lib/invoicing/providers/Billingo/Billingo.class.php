<?php

class Billingo extends InvoiceProvider {
    const PROVIDER_NAME = 'Billingo';

    public static function isAvailable(): bool
    {
        return false;
    }

    public static function getName(): string
    {
        return self::PROVIDER_NAME;
    }

    protected function init(): void
    {
        // TODO: Implement init() method.
    }

    public function getTaxPayer(string $taxNumber): ?InvoiceBuyer
    {
        // TODO: Implement getTaxPayer() method.
        return null;
    }

    public function createInvoice():?string
    {
        // TODO: Implement createInvoice() method.
        return '';
    }

    public function downloadInvoice():?string
    {
        // TODO: Implement downloadInvoice() method.
        return '';
    }

    public function setInvoicePaid(float $amount): bool
    {
        // TODO: Implement setInvoicePaid() method.
        return false;
    }

    public function getInvoice(): ?InvoiceProvider
    {
        // TODO: Implement getInvoice() method.
        return $this;
    }
}