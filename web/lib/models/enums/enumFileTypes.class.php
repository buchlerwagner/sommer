<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumFileTypes
 * @method static enumFileTypes Document()
 * @method static enumFileTypes IncomingInvoice()
 * @method static enumFileTypes OutgoingInvoice()
 * @method static enumFileTypes Contract()
 */
final class enumFileTypes extends Enum {
    private const Document = 'document';
    private const IncomingInvoice = 'invoice_incoming';
    private const OutgoingInvoice = 'invoice_outgoing';
    private const Contract = 'contract';
}