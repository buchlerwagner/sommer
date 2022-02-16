<?php

class KHBank extends PaymentProvider {
    const TYPE_PAYMENT = 'PU';
    const TYPE_REFUND  = 'RE';

    protected function pay():void
    {
        $data = [
            'mid' => $this->settings->shopId,
            'txid' => $this->transactionId,
            'type' => self::TYPE_PAYMENT,
            'amount' => $this->amount * 100,
            'ccy' => strtoupper($this->currency),
        ];

        $data = $this->sign($data);
        $data['lang'] = strtoupper($this->language);

        $url = $this->settings->urlFrontend . '?' . http_build_query($data);
        $this->saveLog($url, 'initPayment');

        header('Location: ' . $url);
        exit();
    }

    protected function check():enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();

        return $status;
    }

    protected function refund()
    {
        // TODO: Implement refund() method.
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

        $data['sign'] =  bin2hex($signature);

        return $data;
    }
}