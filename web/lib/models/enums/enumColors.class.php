<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumColors
 * @method static enumColors Info()
 * @method static enumColors Warning()
 * @method static enumColors Danger()
 * @method static enumColors Success()
 * @method static enumColors Primary()
 * @method static enumColors Secondary()
 */
final class enumColors extends Enum {
    private const Info = 'info';
    private const Warning = 'warning';
    private const Danger = 'danger';
    private const Success = 'success';
    private const Primary = 'primary';
    private const Secondary = 'secondary';
}