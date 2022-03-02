<?php
abstract class PaymentProvider extends ancestor {
    const DEFAULT_TIMEOUT = 20; // minutes

    private $id;
    protected $transactionId;

    /**
     * @var PaymentProviderSettings|null
     */
    protected $settings = null;

    protected $language;
    protected $timeout;

    protected $amount;
    protected $refunded;
    protected $currency;

    protected $status;
    protected $response;
    protected $statusCode;
    protected $authCode;
    protected $message;

    protected abstract function init():void;

    protected abstract function pay():void;

    protected abstract function check():enumPaymentStatus;

    protected abstract function refund(float $amount):enumPaymentStatus;

    public abstract function hasRefund():bool;

    public abstract static function isAvailable():bool;

    public abstract static function getName():string;

    public function __construct(PaymentProviderSettings $settings, $language)
    {
        $this->settings = $settings;
        $this->language = $language;
    }

    final protected function setTimeout(int $timeout):self
    {
        $this->timeout = $timeout;
        return $this;
    }

    final protected function saveResponse(string $response):self
    {
        $this->response = $response;
        return $this;
    }

    final protected function setResult(string $statusCode, string $authCode, string $message = ''):self
    {
        $this->statusCode = $statusCode;
        $this->authCode = $authCode;
        $this->message = $message;

        return $this;
    }

    final public function initPayment(int $id, float $amount, string $currency):void
    {
        $this->init();

        $this->id = $id;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->transactionId = $this->generateTransactionId();

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'payment_transactions',
                [
                    'pt_transactionid' => $this->transactionId,
                    'pt_expiry'        => $this->getTimeout(),
                ],
                [
                    'pt_id'       => $this->id,
                ]
            )
        );

        $this->pay();
    }

    final public function checkTransaction(Transaction $transaction):Transaction
    {
        $this->transactionId = $transaction->transactionId;
        $status = $this->check();

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'payment_transactions',
                [
                    'pt_status' => $status->getValue(),
                    'pt_auth_code' => $this->authCode,
                    'pt_status_code' => $this->statusCode,
                    'pt_message' => $this->message,
                    'pt_response' => $this->response,
                ],
                [
                    'pt_transactionid' => $this->transactionId
                ]
            )
        );

        $transaction->setStatus($status);
        $transaction->authCode = $this->authCode;
        $transaction->message = $this->message;

        return $transaction;
    }

    final public function hasRefundError(Transaction $transaction, float $refundAmount)
    {
        if($transaction->getStatus() != enumPaymentStatus::OK()->getValue()){
            return PaymentException::INVALID_TRANSACTION_STATUS;
        }

        $checkDate = dateAddDays($transaction->created, 1);
        if($checkDate > date('Y-m-d H:i:s')){
            return PaymentException::REFUND_NOT_ALLOWED_YET;
        }

        if($refundAmount > $transaction->amount || $refundAmount == 0) {
            return PaymentException::INVALID_REFUND_AMOUNT;
        }

        return false;
    }

    final public function initRefund(Transaction $transaction, float $refundAmount = 0):Transaction
    {
        $this->init();

        if(!$refundAmount){
            $refundAmount = $transaction->amount;
        }

        if(!$this->hasRefundError($transaction, $refundAmount)) {
            $this->transactionId = $transaction->transactionId;
            $status = $this->refund($refundAmount);

            if($status->getValue() == enumPaymentStatus::Voided()->getValue() || $status->getValue() == enumPaymentStatus::Pending()->getValue()){
                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'payment_transactions',
                        [
                            'pt_status' => $status->getValue(),
                            'pt_response' => $this->response,
                            'pt_auth_code' => $this->authCode,
                            'pt_status_code' => $this->statusCode,
                            'pt_message' => $this->message,
                            'pt_refunded' => $refundAmount,
                        ],
                        [
                            'pt_transactionid' => $this->transactionId
                        ]
                    )
                );
            }

            $transaction->setStatus($status);
            $transaction->amount = $refundAmount;
            $transaction->authCode = $this->authCode;
            $transaction->message = $this->message;
        }

        return $transaction;
    }

    protected function getTimeout():string
    {
        if(!$this->timeout){
            $this->timeout = self::DEFAULT_TIMEOUT;
        }

        return date('Y-m-d H:i:s', time() + ($this->timeout * 60));
    }

    protected function generateTransactionId():string
    {
        return uniqid();
    }

    protected function saveLog($data, $action, $method = 'rq'):void
    {
        if ( defined('DIR_LOG') ) {
            $action = str_replace('_', '', $action);

            if(is_array($data)){
                $data = json_encode($data, JSON_PRETTY_PRINT);
            }

            $fileName      = (microtime(true) * 10000) . '_' . $action . '_' . $this->transactionId . '_'. $method . '.txt';
            $folderName    = DIR_LOG . 'payments/' . $this->owner->shopId . '/' . strtolower( get_class( $this ) ) . '/' . date( 'Ym' ) . '/' . date( 'd' ) . '/';

            if(!is_dir($folderName)){
                @mkdir($folderName, 0777, true);
                @chmod($folderName, 0777);
            }

            @file_put_contents( $folderName . '/' . $fileName, $data, FILE_APPEND );
        }
    }

}
