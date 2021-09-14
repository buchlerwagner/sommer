<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumTableRowTypes
 * @method static enumTableColTypes General()
 * @method static enumTableColTypes Hidden()
 * @method static enumTableColTypes Options()
 * @method static enumTableColTypes Checkbox()
 * @method static enumTableColTypes Radio()
 * @method static enumTableColTypes CheckboxSlider()
 */
final class enumTableColTypes extends Enum {
    private const General = false;
    private const Hidden = 'hidden';
    private const Options = 'options';
    private const Checkbox = 'checkbox';
    private const Radio = 'radio';
    private const CheckboxSlider = 'checkboxslider';
}