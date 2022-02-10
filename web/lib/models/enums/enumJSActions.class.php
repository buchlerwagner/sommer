<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumJSActions
 * @method static enumJSActions SetHtml()
 * @method static enumJSActions ShowHideElement()
 * @method static enumJSActions AddClass()
 * @method static enumJSActions RemoveClass()
 * @method static enumJSActions RemoveNode()
 * @method static enumJSActions CloseModal()
 * @method static enumJSActions SetAttribute()
 * @method static enumJSActions SetValue()
 * @method static enumJSActions SetOptions()
 * @method static enumJSActions CallFunction()
 * @method static enumJSActions CallMethod()
 */
final class enumJSActions extends Enum {
    private const SetHtml = 'html';
    private const ShowHideElement = 'show';
    private const AddClass = 'addclass';
    private const RemoveClass = 'removeclass';
    private const RemoveNode = 'remove';
    private const CloseModal = 'closeModal';
    private const SetAttribute = 'attr';
    private const SetValue = 'value';
    private const SetOptions = 'options';
    private const CallFunction = 'functions';
    private const CallMethod = false;
}