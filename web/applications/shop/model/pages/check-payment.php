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
     * @var $payment Payments
     */
    $payment = $this->addByClassName('Payments');

    $transaction = $payment->checkTransaction($transactionId);

    if($transaction->cartKey){
        if($transaction->getStatus() !== enumPaymentStatus::Pending()->getValue()){
            $this->cart->init($transaction->cartKey, false);

            if($transaction->getStatus() === enumPaymentStatus::OK()->getValue()) {
                $this->cart->setPaid();
            }

            if($transaction->getStatus() !== enumPaymentStatus::Pending()->getValue()){
                $this->cart->sendPaymentConfirmationEmail($transaction);
            }
        }

        $this->pageRedirect($this->getPageName('finish') . $transaction->cartKey . '/');
    }
}

$this->pageRedirect($this->getPageName('payment-error') . '?trid=' . ($transactionId ?: ''));