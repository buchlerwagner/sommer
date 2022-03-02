<?php
class Payments extends ancestor {
    private $providerId;
    private $language;

    /**
     * @var PaymentProvider
     */
    private $provider = null;

    public static function getProviders():array
    {
        $result = [];

        $directory = new RecursiveDirectoryIterator(__DIR__ . '/providers/');
        $flattened = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($flattened, '/(\.class)(\.php)$/');

        foreach($files as $file) {
            /**
             * @var $classname PaymentProvider
             */
            $classname = substr(basename($file), 0, -10);
            if($classname::isAvailable()) {
                $result[$classname] = $classname::getName();
            }
        }

        return $result;
    }

    public function init(int $paymentProviderId, string $language = ''):self
    {
        $this->providerId = $paymentProviderId;
        $this->language = $language;

        if(Empty($this->language)){
            $this->language = $this->owner->language;
        }

        if($settings = $this->loadSettings()){
            $this->provider = $this->owner->addByClassName($settings->className, false, [
                $settings,
                $this->language
            ]);
        }else{
            throw new PaymentException(PaymentException::INVALID_PAYMENT_PROVIDER_ID);
        }

        return $this;
    }

    public function createTransaction(int $cartId, float $amount, string $currency):void
    {
        if(!$this->provider){
            throw new PaymentException(PaymentException::PAYMENT_PROVIDER_NOT_INITED);
        }
        if(!$this->hasPendingTransaction($cartId)){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLInsert(
                    'payment_transactions',
                    [
                        'pt_shop_id'       => $this->owner->shopId,
                        'pt_cart_id'       => $cartId,
                        'pt_pp_id'         => $this->providerId,
                        'pt_created'       => 'NOW()',
                        'pt_language'      => $this->language,
                        'pt_status'        => enumPaymentStatus::Pending()->getValue(),
                        'pt_ip'            => $_SERVER['REMOTE_ADDR'],
                        'pt_amount'        => $amount,
                        'pt_currency'      => $currency
                    ]
                )
            );

            $this->provider->initPayment(
                $this->owner->db->getInsertRecordId(),
                $amount,
                $currency
            );
        }
    }

    public function checkTransaction(string $transactionId):Transaction
    {
        if(!Empty($transactionId)){
            if($transaction = $this->getTransaction($transactionId)) {
                try {
                    $this->init($transaction->providerId);
                    $transaction = $this->provider->checkTransaction($transaction);
                }

                catch(PaymentException $e){

                }
            }
        }else{
            $transaction = new Transaction();
        }

        return $transaction;
    }

    public function refund(string $transactionId, float $refundAmount = 0):bool
    {
        $success = false;

        if(!Empty($transactionId)){
            if($transaction = $this->getTransaction($transactionId)) {
                $this->init($transaction->providerId);
                $transaction = $this->provider->initRefund($transaction, $refundAmount);

                $success = ($transaction->getStatus() == enumPaymentStatus::Voided());
            }
        }

        return $success;
    }

    private function loadSettings():?PaymentProviderSettings
    {
        $settings = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_providers',
                [
                    'pp_id AS id',
                    'pp_name AS name',
                    'pp_provider AS className',
                    'pp_shop_id AS shopId',
                    'pp_merchant_id AS merchantId',
                    'pp_password AS password',
                    'pp_currency AS currency',
                    'pp_test_mode AS isTest',
                    'pp_url_frontend AS urlFrontend',
                    'pp_url_return AS urlReturn',
                    'pp_url_backend AS urlCallback',
                    'pp_private_key AS privateKey',
                ],
                [
                    'pp_id' => $this->providerId,
                    'pp_shop_id' => $this->owner->shopId,
                ]
            )
        );

        return ($settings ? new PaymentProviderSettings($settings) : null);
    }

    public function hasPendingTransaction(int $cartId):bool
    {
        $transaction = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_transactionid AS transactionId'
                ],
                [
                    'pt_cart_id' => $cartId,
                    'pt_shop_id' => $this->owner->shopId,
                    'pt_status'  => enumPaymentStatus::Pending()->getValue(),
                ]
            )
        );

        return (bool) $transaction;
    }

    public function getTransactionHistory(int $cartId):array
    {
        $out = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_id AS id',
                    'pt_created AS created',
                    'pt_status AS status',
                    'pt_transactionid AS transactionId',
                    'pt_auth_code AS authCode',
                    'pt_amount AS amount',
                    'pt_currency AS currency',
                    'pt_message AS message',
                ],
                [
                    'pt_cart_id' => $cartId,
                    'pt_shop_id' => $this->owner->shopId,
                ],
                [],
                false,
                'pt_created DESC'
            )
        );

        return ($out ?: []);
    }

    public function getTransaction(string $transactionId):?Transaction
    {
        $out = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_pp_id AS providerId',
                    'pt_id AS id',
                    'pt_created AS created',
                    'pt_status AS status',
                    'pt_transactionid AS transactionId',
                    'pt_auth_code AS authCode',
                    'pt_amount AS amount',
                    'pt_currency AS currency',
                    'pt_message AS message',

                    'pt_cart_id AS cartId',
                    'cart_key AS cartKey',
                ],
                [
                    'pt_transactionid' => $transactionId,
                    'pt_shop_id' => $this->owner->shopId,
                ],
                [
                    'cart' => [
                        'on' => [
                            'cart_id' => 'pt_cart_id'
                        ]
                    ]
                ]
            )
        );

        return ($out ? new Transaction($out) : null);
    }

    public function getRefundableTransaction(int $cartId):?Transaction
    {
        $out = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_pp_id AS providerId',
                    'pt_id AS id',
                    'pt_created AS created',
                    'pt_status AS status',
                    'pt_transactionid AS transactionId',
                    'pt_auth_code AS authCode',
                    'pt_amount AS amount',
                    'pt_currency AS currency',
                    'pt_message AS message'
                ],
                [
                    'pt_cart_id' => $cartId,
                    'pt_shop_id' => $this->owner->shopId,
                    'pt_status'  => enumPaymentStatus::OK()->getValue(),
                ]
            )
        );

        return ($out ? new Transaction($out) : null);
    }
}
