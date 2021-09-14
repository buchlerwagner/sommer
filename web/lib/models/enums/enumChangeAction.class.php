<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumAlertLevels
 * @method static enumChangeAction Show()
 * @method static enumChangeAction Hide()
 * @method static enumChangeAction Disable()
 * @method static enumChangeAction Enable()
 * @method static enumChangeAction Readonly()
 * @method static enumChangeAction Editable()
 * @method static enumChangeAction Value()
 */
final class enumChangeAction extends Enum {
    private const Show = 'show';
    private const Hide = 'hide';
    private const Disable = 'disable';
    private const Enable = 'enable';
    private const Readonly = 'readonly';
    private const Editable = 'editable';
    private const Value = 'value';
}