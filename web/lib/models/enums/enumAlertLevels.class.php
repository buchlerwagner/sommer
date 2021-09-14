<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumAlertLevels
 * @method static enumAlertLevels Info()
 * @method static enumAlertLevels Warning()
 * @method static enumAlertLevels Danger()
 * @method static enumAlertLevels Success()
 */
final class enumAlertLevels extends Enum {
    private const Info = 'info';
    private const Warning = 'warning';
    private const Danger = 'danger';
    private const Success = 'success';
}