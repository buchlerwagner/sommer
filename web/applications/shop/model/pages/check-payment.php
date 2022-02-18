<?php
/**
 * @var $this router
 */

$transactionId = false;

// General payment check
if(!Empty($_GET['trid'])){
    $transactionId = urldecode($_GET['trid']);
}

// K&H Bank
if(!Empty($_GET['txid'])){
    $transactionId = urldecode($_GET['txid']);
}

if($transactionId) {
    /**
     * @var $paymentHandler PaymentHandler
     */
    $paymentHandler = $this->addByClassName('PaymentHandler');
    $transaction = $paymentHandler->handleTransaction($transactionId);

    if($transaction->cartKey){
        $this->pageRedirect($this->getPageName('finish') . $transaction->cartKey . '/');
    }
}

$this->pageRedirect($this->getPageName('payment-error') . '?trid=' . ($transactionId ?: ''));