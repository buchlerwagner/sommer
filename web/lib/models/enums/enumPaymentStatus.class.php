<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumPaymentStatus
 * @method static enumPaymentStatus OK()
 * @method static enumPaymentStatus Failed()
 * @method static enumPaymentStatus Canceled()
 * @method static enumPaymentStatus Error()
 * @method static enumPaymentStatus Timeout()
 * @method static enumPaymentStatus Pending()
 */
final class enumPaymentStatus extends Enum {
    private const OK         = 'OK';
    private const Failed     = 'FAILED';
    private const Canceled   = 'CANCELED';
    private const Error      = 'ERROR';
    private const Timeout    = 'TIMEOUT';
    private const Pending    = 'PENDING';
}