<?php
class Payments extends ancestor {
    private $providerId;
    private $language;
    private $cartKey = '';

    /**
     * @var PaymentProvider
     */
    private $provider = null;

    public static function getPaymentProviders():array
    {
        $result = [];

        $directory = new RecursiveDirectoryIterator(__DIR__ . '/providers/');
        $flattened = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($flattened, '/(\.class)(\.php)$/');

        foreach($files as $file) {
            $classname = substr(basename($file), 0, -10);
            $result[ $classname ] = $classname;
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
            throw new Exception('Invalid payment provider ID');
        }

        return $this;
    }

    public function getCartKey():string
    {
        return $this->cartKey;
    }

    public function createTransaction(int $cartId, float $amount, string $currency):void
    {
        if(!$this->provider){
            throw new Exception('Payment provider is not initialised!');
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

    public function checkPendingPayments()
    {

    }

    public function checkTransaction(string $transactionId):enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();

        if(!Empty($transactionId)){
            if($transaction = $this->getTransaction($transactionId)) {
                $this->cartKey = $transaction['cartKey'];

                try {
                    $this->init($transaction['providerId']);
                    $status = $this->provider->checkTransaction($transactionId);
                }

                catch(Exception $e){

                }
            }
        }

        return $status;
    }

    public function refund(string $transactionId)
    {
        if(!$this->provider){
            throw new Exception('Payment provider is not initialised!');
        }

    }

    private function loadSettings():PaymentProviderSettings
    {
        $settings = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_providers',
                [
                    'pp_id AS id',
                    'pp_name AS name',
                    'pp_provider AS className',
                    'pp_shopid AS shopId',
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

        return new PaymentProviderSettings(($settings ?: []));
    }

    public function hasPendingTransaction($cartId):int
    {
        $out = 0;

        $transaction = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_id AS id'
                ],
                [
                    'pt_cart_id' => $cartId,
                    'pt_shop_id' => $this->owner->shopId,
                    'pt_status'  => enumPaymentStatus::Pending()->getValue(),
                ]
            )
        );

        if($transaction){
            $out = $transaction['id'];
        }

        return $out;
    }

    private function getTransaction(string $transactionId):array
    {
        $out = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_transactions',
                [
                    'pt_id AS id',
                    'pt_transactionid AS transactionId',
                    'pt_cart_id AS cartId',
                    'pt_pp_id AS providerId',
                    'pt_status AS status',
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

        return ($out ?: []);
    }
}
