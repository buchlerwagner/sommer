<?php
abstract class PaymentProvider extends ancestor {
    const DEFAULT_TIMEOUT = 20; // minutes

    private $id;
    protected $transactionId;
    protected $providerTransactionId;

    /**
     * @var PaymentProviderSettings|null
     */
    protected $settings = null;

    protected $language;
    protected $timeout;

    protected $amount;
    protected $refundAmount = 0;
    protected $refunded;
    protected $currency;

    protected $status;
    protected $response;
    protected $statusCode;
    protected $authCode;
    protected $message;

    protected $params = [];

    protected abstract function init():void;

    protected abstract function onBeforePayment():void;

    protected abstract function onAfterPayment():void;

    protected abstract function pay():void;

    protected abstract function check():enumPaymentStatus;

    protected abstract function refund(float $amount, Transaction $transaction):enumPaymentStatus;

    public abstract function callback($data = []):enumPaymentStatus;

    public abstract function sendCallbackResponse($data = []):void;

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

    final protected function setResult(string $providerTransactionId, string $statusCode, string $authCode, string $message = ''):self
    {
        $this->providerTransactionId = $providerTransactionId;
        $this->statusCode = $statusCode;
        $this->authCode = $authCode;
        $this->message = $message;

        return $this;
    }

    final public function initPayment(int $id, float $amount, string $currency, array $params = []):void
    {
        $this->init();

        $this->id = $id;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->params = $params;
        $this->transactionId = $this->generateTransactionId();

        $this->onBeforePayment();

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'payment_transactions',
                [
                    'pt_transactionid' => $this->transactionId,
                    'pt_provider_transactionid' => $this->providerTransactionId,
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
        $this->init();

        $this->transactionId = $transaction->transactionId;
        $this->providerTransactionId = $transaction->providerTransactionId;

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

    final public function processCallback(Transaction $transaction, $data = []):void
    {
        $this->init();

        $this->transactionId = $transaction->transactionId;
        $this->providerTransactionId = $transaction->providerTransactionId;

        $status = $this->callback($data);

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'payment_transactions',
                [
                    'pt_status' => $status->getValue(),
                    'pt_callback_response' => $data,
                    'pt_callback_timestamp' => 'NOW()',
                ],
                [
                    'pt_transactionid' => $this->transactionId
                ]
            )
        );

        $this->sendCallbackResponse($data);
    }

    final public function hasRefundError(Transaction $transaction, float $refundAmount)
    {
        if($transaction->getStatus() != enumPaymentStatus::OK()->getValue()){
            return PaymentException::INVALID_TRANSACTION_STATUS;
        }

        $checkDate = dateAddDays($transaction->created, 1);
        if($checkDate > date('Y-m-d H:i:s')){
            //return PaymentException::REFUND_NOT_ALLOWED_YET;
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
            $status = $this->refund($refundAmount, $transaction);

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
        return uuid::v4();
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

    protected function getParam(string $key)
    {
        return ($this->params[$key] ?: false);
    }

}
