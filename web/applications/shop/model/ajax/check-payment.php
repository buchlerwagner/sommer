<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;
$transactionId = trim($this->params[1]);

if(!Empty($transactionId)){
    /**
     * @var $paymentHandler PaymentChecker
     */
    $paymentHandler = $this->addByClassName('PaymentChecker');
    $transaction = $paymentHandler->handleTransaction($transactionId);

    switch ($transaction->getStatus()){
        case enumPaymentStatus::Pending()->getValue():
            $this->data = [
                'pending' => true,
            ];
            break;
        case enumPaymentStatus::OK()->getValue():
            $this->data = [
                'title' => $this->translate->getTranslation('LBL_PAYMENT_SUCCESS'),
                'pending' => false,
                'success' => true,
                'transactionId' => $transaction->transactionId,
                'authCode' => $transaction->authCode,
                'message' => $transaction->message,
            ];
            break;
        default:
            $this->data = [
                'title' => $this->translate->getTranslation('LBL_PAYMENT_FAILED'),
                'pending' => false,
                'success' => false,
                'transactionId' => $transaction->transactionId,
                'message' => $transaction->message,
            ];
            break;
    }

}