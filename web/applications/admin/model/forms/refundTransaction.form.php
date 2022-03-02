<?php
class refundTransactionForm extends formBuilder {

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var Payments
     */
    private $payment;

    public function setupKeyFields() {
        $this->setKeyFields(['cart_id']);
    }

    public function setup() {
        $this->title = 'LBL_REFUND_TRANSACTION';

        $this->reloadPage = true;

        $amount = 0;
        $this->payment = $this->owner->addByClassName('Payments');

        if($this->owner->user->hasFunctionAccess('orders-refund')){
            if($this->transaction = $this->payment->getRefundableTransaction($this->keyFields['cart_id'])){
                $amount = $this->transaction->amount;

                $this->payment->init($this->transaction->providerId);
            }else{
                $this->readonly = true;
            }
        }else{
            $this->readonly = true;
        }

        $this->addControls(
            (new groupRow('row1'))->addElements(
                (new inputText('amount', 'LBL_REFUND_AMOUNT', $amount))
                    ->setColSize('col-6 col-lg-5')
                    ->setMaskDecimal()
                    ->onlyNumbers()
                    ->addClass('text-right')
                    ->setAppend($this->owner->currencySign)
                    ->setRequired()
            )
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if(abs($this->values['amount']) > 0){
            try{
                $this->payment->isValidRefundRequest($this->transaction, abs($this->values['amount']));
            }catch (PaymentException $e){
                $this->addError($e->getMessage(), self::FORM_ERROR, ['amount']);
            }
        }
    }

    public function onBeforeSave() {
        try{
            if($this->payment->isValidRefundRequest($this->transaction, abs($this->values['amount']))) {
                $this->transaction = $this->payment->refund($this->transaction->transactionId, abs($this->values['amount']));

                if($this->transaction->getStatus() == enumPaymentStatus::Voided()->getValue()) {
                    $this->owner->cartHandler->init($this->keyFields['cart_id'], false);
                    $this->owner->cartHandler->setRefunded(abs($this->values['amount']));
                    $this->owner->cartHandler->sendPaymentConfirmationEmail($this->transaction);

                    $this->owner->addMessage(router::MESSAGE_SUCCESS, 'LBL_REFUND', 'LBL_REFUND_SUCCESS');
                }elseif($this->transaction->getStatus() == enumPaymentStatus::Pending()->getValue()){
                    $this->owner->addMessage(router::MESSAGE_WARNING, 'LBL_REFUND', 'LBL_REFUND_PENDING');
                }else{
                    $this->owner->addMessage(router::MESSAGE_DANGER, 'LBL_REFUND_ERROR', 'LBL_REFUND_FAILED');
                }
            }
        }catch (PaymentException $e){
            $this->owner->addMessage(router::MESSAGE_DANGER, 'LBL_REFUND_ERROR', $e->getMessage());
        }
    }
}
