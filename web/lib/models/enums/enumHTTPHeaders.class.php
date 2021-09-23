<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumFormSizes
 * @method static enumHTTPHeaders MovedPermanently301()
 * @method static enumHTTPHeaders Forbidden403()
 * @method static enumHTTPHeaders NotFound404()
 */
final class enumHTTPHeaders extends Enum {
    private const MovedPermanently301 = 'HTTP/1.1 301 Moved Permanently';
    private const Forbidden403 = 'HTTP/1.1 403 Forbidden';
    private const NotFound404 = 'HTTP/1.1 404 Not Found';
}