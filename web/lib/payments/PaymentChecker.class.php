<?php

class PaymentChecker extends ancestor {

    public function handleTransaction(string $transactionId):Transaction
    {
        /**
         * @var $payment Payments
         */
        $payment = $this->owner->addByClassName('Payments', false, [], true);

        $transaction = $payment->checkTransaction($transactionId);

        $this->updateCart($transaction);

        return $transaction;
    }

    private function updateCart(Transaction $transaction):void
    {
        if($transaction->cartKey){
            if($transaction->getStatus() !== enumPaymentStatus::Pending()->getValue()){
                $this->owner->cart->init($transaction->cartKey, false);

                if($transaction->getStatus() === enumPaymentStatus::OK()->getValue()) {
                    $this->owner->cart->setPaid();
                }

                if($transaction->getStatus() !== enumPaymentStatus::Pending()->getValue()){
                    $this->owner->cart->sendPaymentConfirmationEmail($transaction);
                }
            }
        }
    }

    public function checkPendingTransactions():void
    {

    }
}