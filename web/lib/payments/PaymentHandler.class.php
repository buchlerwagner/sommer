<?php

class PaymentHandler extends ancestor {

    public function handleTransaction(string $transactionId):?Transaction
    {
        /**
         * @var $payment Payments
         */
        $payment = $this->owner->addByClassName('Payments', false, [], true);

        if($transaction = $payment->checkTransaction($transactionId)) {
            $this->updateCart($transaction);
        }

        return $transaction;
    }

    public function handleCallback(string $transactionId, $data = []):void
    {
        /**
         * @var $payment Payments
         */
        $payment = $this->owner->addByClassName('Payments', false, [], true);
        $payment->processCallback($transactionId, $data);
    }

    private function updateCart(Transaction $transaction):void
    {
        if($transaction->cartKey){
            if($transaction->getStatus() !== enumPaymentStatus::Pending()->getValue()){
                $this->owner->cartHandler->init($transaction->cartKey, false);

                if($transaction->getStatus() === enumPaymentStatus::OK()->getValue()) {
                    $this->owner->cartHandler->setPaid();

                    // Issue invoice after successful bank card payment
                    $this->owner->cartHandler->issueInvoice();
                }

                if($transaction->getStatus() === enumPaymentStatus::Voided()->getValue()) {
                    $this->owner->cartHandler->setRefunded($transaction->amount);
                }

                if($transaction->getStatus() !== enumPaymentStatus::Pending()->getValue()){
                    $this->owner->cartHandler->sendPaymentConfirmationEmail($transaction);
                }
            }
        }
    }

    public function checkPendingTransactions():void
    {
        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_transactionid AS transactionId'
                ],
                [
                    'pt_status'  => enumPaymentStatus::Pending()->getValue(),
                    'pt_expiry' => [
                        'less' => 'NOW()'
                    ]
                ]
            )
        );
        if($result){
            foreach($result AS $transaction){
                $this->handleTransaction($transaction['transactionId']);
            }
        }
    }
}