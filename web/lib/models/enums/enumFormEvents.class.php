<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumAlertLevels
 * @method static enumFormEvents onClick()
 * @method static enumFormEvents onChange()
 * @method static enumFormEvents onMouseOver()
 * @method static enumFormEvents onBlur()
 * @method static enumFormEvents onFocus()
 * @method static enumFormEvents onKeyDown()
 * @method static enumFormEvents onKeyUp()
 * @method static enumFormEvents onKeyPress()
 */
final class enumFormEvents extends Enum {
    private const onClick = 'onclick';
    private const onChange = 'onchange';
    private const onMouseOver = 'onmouseover';
    private const onBlur = 'onblur';
    private const onFocus = 'onfocus';
    private const onKeyDown = 'onkeydown';
    private const onKeyUp = 'onkeyup';
    private const onKeyPress = 'onkeypress';
}