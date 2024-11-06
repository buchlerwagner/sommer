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

// OTP Simplepay
if(!Empty($_GET['r']) && !Empty($_GET['s'])){
    $json = base64_decode($_GET['r']);
    $json = json_decode($json, true);
    $transactionId = $json['o'];
}

// Barion
if(!Empty($_GET['paymentId'])){
    $paymentId = urldecode($_GET['paymentId']);

    $result = $this->db->getFirstRow(
        $this->db->genSQLSelect(
            'payment_transactions',
            [
                'pt_transactionid'
            ],
            [
                'pt_provider_transactionid' => $paymentId,
            ]
        )
    );

    if(!Empty($result['pt_transactionid'])){
        $transactionId = $result['pt_transactionid'];
    }
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