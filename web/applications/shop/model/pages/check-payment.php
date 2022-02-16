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

    $status = $payment->checkTransaction($transactionId);

    if($key = $payment->getCartKey()){
        if($status->getValue() !== enumPaymentStatus::Pending()->getValue()){
            $this->cart->init($key, false);

            if($status->getValue() === enumPaymentStatus::OK()->getValue()) {
                $this->cart->setPaid();
            }

            if($status !== enumPaymentStatus::Pending()){
                $this->cart->sendPaymentConfirmationEmail();
            }
        }

        $this->pageRedirect($this->getPageName('finish') . $key . '/');
    }
}

$this->pageRedirect($this->getPageName('payment-error') . '?trid=' . ($transactionId ?: ''));