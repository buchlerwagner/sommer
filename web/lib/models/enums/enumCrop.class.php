<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumCrop
 * @method static enumCrop Simple()
 * @method static enumCrop Adaptive()
 * @method static enumCrop Center()
 */
final class enumCrop extends Enum {
    private const Simple = false;
    private const Adaptive = 1;
    private const Center = 2;
}