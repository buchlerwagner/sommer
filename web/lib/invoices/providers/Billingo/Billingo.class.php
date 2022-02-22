<?php

class Billingo extends InvoiceProvider {

    public static function isAvailable(): bool {
        return false;
    }

    public static function getName(): string {
        return 'Billingo';
    }
}