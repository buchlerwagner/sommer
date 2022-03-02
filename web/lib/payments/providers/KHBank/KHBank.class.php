<?php

class KHBank extends PaymentProvider {
    const TIME_OUT = 5; // minutes

    const TYPE_PAYMENT = 'PU';
    const TYPE_REFUND  = 'RE';

    const HAS_REFUND  = true;

    public static function isAvailable(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'K&H Bank Payment Gateway';
    }

    public function hasRefund(): bool
    {
        return self::HAS_REFUND;
    }

    protected function init(): void
    {
        $this->setTimeout(self::TIME_OUT);
    }

    protected function pay():void
    {
        $amount = $this->amount * 100;

        $data = [
            'txid'      => $this->transactionId,
            'type'      => self::TYPE_PAYMENT,
            'mid'       => $this->settings->merchantId,
            'amount'    => $amount,
            'ccy'       => strtoupper($this->currency),
            'sign'      => $this->sign($amount, self::TYPE_PAYMENT),
            'lang'      => strtoupper($this->language)
        ];

        $url = rtrim($this->settings->urlFrontend, '/') . '/PGPayment?' . http_build_query($data);
        $this->saveLog($url, 'initPayment');

        header('Location: ' . $url);
        exit();
    }

    protected function check():enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();

        $data = [
            'mid' => $this->settings->merchantId,
            'txid' => $this->transactionId
        ];

        $url = rtrim($this->settings->urlFrontend, '/') . '/PGResult?' . http_build_query($data);
        $this->saveLog($url, 'checkPayment');

        $result = $this->sendRequest($url);
        $this->saveLog($result, 'checkPayment', 'rs');

        if (!empty($result)) {
            $status = $this->getStatus($result);
        }

        return $status;
    }

    protected function refund(float $amount):enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();
        $amount *= 100;

        $data = [
            'txid'      => $this->transactionId,
            'type'      => self::TYPE_REFUND,
            'mid'       => $this->settings->merchantId,
            'amount'    => $amount,
            'ccy'       => strtoupper($this->currency),
            'sign'      => $this->sign($amount, self::TYPE_REFUND),
        ];

        $url = rtrim($this->settings->urlFrontend, '/') . '/PGPayment?' . http_build_query($data);
        $this->saveLog($url, 'voidPayment');

        $result = $this->sendRequest($url);
        $this->saveLog($result, 'voidPayment', 'rs');

        if (!empty($result)) {
            $status = $this->getStatus($result);
        }

        return $status;
    }

    protected function generateTransactionId():string
    {
        $result = mt_rand(1, 9);
        for ($i = 1; $i <= 9; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }

    private function sign(float $amount, string $type):string
    {
        $data = [
            'mid' => $this->settings->merchantId,
            'txid' => $this->transactionId,
            'type' => $type,
            'amount' => $amount,
            'ccy' => strtoupper($this->currency),
        ];

        $privateKey = $this->settings->getPrivateKey();
        if($privateKey) {
            $pkeyId = openssl_get_privatekey($privateKey);

            // compute signature
            openssl_sign(http_build_query($data), $signature, $pkeyId);

            // free the key from memory
            openssl_free_key($pkeyId);

            return bin2hex($signature);
        }else{
            return '';
        }
    }

    private function sendRequest(string $url):string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $result = curl_exec($ch);

        if (curl_error($ch)) {
            error_log( curl_error($ch) );
        }

        curl_close($ch);

        return $result;
    }

    private function readResponse(string $response):array
    {
        $result = explode("\n", $response);

        return [
            'status' => strtoupper(trim($result[0])),
            'message' => trim($result[2]),
            'authCode' => trim($result[3]),
        ];
    }

    private function getStatus(string $result):enumPaymentStatus
    {
        $this->saveResponse($result);

        $response = $this->readResponse($result);

        if(!$response['status']) $response['status'] = '';
        if(!$response['authCode']) $response['authCode'] = '';
        if(!$response['message']) $response['message'] = '';

        $this->setResult($response['status'], $response['authCode'], $response['message']);

        switch($response['status']){
            case 'ACK':
                $status = enumPaymentStatus::OK();
                break;
            case 'CAN':
                $status = enumPaymentStatus::Canceled();
                break;
            case 'EXP':
                $status = enumPaymentStatus::Timeout();
                break;
            case 'VOI':
                $status = enumPaymentStatus::Voided();
                break;
            case 'PE2':
            case 'PEN':
                $status = enumPaymentStatus::Pending();
                break;
            case 'NAK':
            case 'UTX':
            case 'ERR':
            default:
                $status = enumPaymentStatus::Failed();
                break;
        }

        return $status;
    }
}