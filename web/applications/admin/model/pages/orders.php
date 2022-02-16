<?php
/**
 * @var $this router
 */

if(isset($_REQUEST['print']) && $this->params[1]){
    /**
     * @var $doc PrintOrder
     */

    $cartId = (int) $this->params[1];
    $doc = $this->addByClassName('PrintOrder');
    $doc->setCart($this->cart->init($cartId, false));
    $doc->print()->getPDF();
}

if(isset($_REQUEST['mail']) && $this->params[1]){
    $cartId = (int) $this->params[1];
    if($this->cart->init($cartId, false)->sendConfirmationEmail(true)){
        $this->addMessage(router::MESSAGE_SUCCESS, '', 'LBL_ORDER_MAIL_SENT_SUCCESSFULLY');
    }else {
        $this->addMessage(router::MESSAGE_DANGER, '', 'LBL_ORDER_MAIL_UNABLE_TO_SENT');
    }

    $this->pageRedirect('/orders/view|orders/' . $cartId . '/');
}

$this->data['filterForm'] = $this->loadForm('orderFilters');
$this->data['table'] = $this->loadTable('orders');

$this->page = 'page-table';
