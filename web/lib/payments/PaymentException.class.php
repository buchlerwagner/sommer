<?php

class PaymentException extends \Exception {
    const INVALID_TRANSACTION_ID = 'LBL_INVALID_TRANSACTION_ID';
    const INVALID_TRANSACTION_STATUS = 'LBL_INVALID_TRANSACTION_STATUS';
    const INVALID_REFUND_AMOUNT = 'LBL_INVALID_REFUND_AMOUNT';
    const REFUND_NOT_ALLOWED_YET = 'LBL_REFUND_NOT_ALLOWED_YET';

    const INVALID_PAYMENT_PROVIDER_ID = 'LBL_INVALID_PAYMENT_PROVIDER_ID';
    const PAYMENT_PROVIDER_NOT_INITED = 'LBL_PAYMENT_PROVIDER_NOT_INITED';


    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}