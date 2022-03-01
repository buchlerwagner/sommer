<?php

class KHBank extends PaymentProvider {
    const TYPE_PAYMENT = 'PU';
    const TYPE_REFUND  = 'RE';

    public static function isAvailable(): bool {
        return true;
    }

    public static function getName(): string {
        return 'K&H Bank Payment Gateway';
    }

    protected function pay():void
    {
        $data = [
            'txid' => $this->transactionId,
            'type' => self::TYPE_PAYMENT,
            'mid' => $this->settings->shopId,
            'amount' => $this->amount * 100,
            'ccy' => strtoupper($this->currency),
        ];

        $data['sign'] = $this->sign($data);
        $data['lang'] = strtoupper($this->language);

        $url = rtrim($this->settings->urlFrontend, '/') . '/PGPayment?' . http_build_query($data);
        $this->saveLog($url, 'initPayment');

        header('Location: ' . $url);
        exit();
    }

    protected function check():enumPaymentStatus
    {
        $data = [
            'mid' => $this->settings->shopId,
            'txid' => $this->transactionId
        ];

        $url = rtrim($this->settings->urlFrontend, '/') . '/PGResult?' . http_build_query($data);
        $this->saveLog($url, 'checkPayment');

        $status = enumPaymentStatus::Pending();

        return $status;
    }

    protected function refund(float $amount):enumPaymentStatus
    {
        return enumPaymentStatus::Failed();
    }

    protected function generateTransactionId():string
    {
        $result = mt_rand(1, 9);
        for ($i = 0; $i <= 9; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    private function sign(array $data):array
    {
        $fp = fopen($this->settings->getPrivateKey(), 'r');

        $privateKey = fread($fp, 8192);
        fclose($fp);
        $pkeyId = openssl_get_privatekey($privateKey);

        // compute signature
        openssl_sign($data, $signature, $pkeyId);

        // free the key from memory
        openssl_free_key($pkeyId);

        return bin2hex($signature);
    }
}