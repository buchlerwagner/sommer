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
    $doc->setCart($this->cartHandler->init($cartId, false));
    $doc->print()->getPDF();
}

if(isset($_REQUEST['mail']) && $this->params[1]){
    $cartId = (int) $this->params[1];
    if($this->cartHandler->init($cartId, false)->sendConfirmationEmail(true)){
        $this->addMessage(router::MESSAGE_SUCCESS, '', 'LBL_ORDER_MAIL_SENT_SUCCESSFULLY');
    }else {
        $this->addMessage(router::MESSAGE_DANGER, '', 'LBL_ORDER_MAIL_UNABLE_TO_SENT');
    }

    $this->pageRedirect('/orders/view|orders/' . $cartId . '/');
}

if(isset($_REQUEST['paid']) && $this->params[1]) {
    $cartId = (int) $this->params[1];
    $this->cartHandler->init($cartId, false);
    $this->addMessage(router::MESSAGE_SUCCESS, 'LBL_INVOICE', 'LBL_ORDER_MARKED_AS_PAID');
    $cart = $this->cartHandler->getCart();

    if($cart->getInvoiceNumber()) {
        /**
         * @var $invoice Invoices
         */
        $invoice = $this->addByClassName('Invoices');
        if ($invoice->hasInvoiceProvider()) {
            try {
                $result = $invoice->init($cart)->setPaid();
                if ($result) {
                    $this->addMessage(router::MESSAGE_SUCCESS, 'LBL_INVOICE', 'LBL_INVOICE_AGENT_PAID');
                } else {
                    $this->addMessage(router::MESSAGE_WARNING, 'LBL_INVOICE', 'LBL_INVOICE_AGENT_PAID_FAILED');
                }
            } catch (Exception $e) {
                $this->addMessage(router::MESSAGE_DANGER, 'LBL_INVOICE_ERROR', $e->getMessage());
            }
        }
    }

    $this->cartHandler->setPaid();

    $this->pageRedirect('/orders/view|orders/' . $cartId . '/');
}

$this->data['filterForm'] = $this->loadForm('orderFilters');
$this->data['table'] = $this->loadTable('orders');

$this->page = 'page-table';
