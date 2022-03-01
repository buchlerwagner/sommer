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
            'txid'      => $this->transactionId,
            'type'      => self::TYPE_PAYMENT,
            'mid'       => $this->settings->merchantId,
            'amount'    => $this->amount * 100,
            'ccy'       => strtoupper($this->currency),
            'sign'      => $this->sign(),
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
        $this->saveLog($result, 'checkPayment', 'rs');

        if (!empty($result)) {
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
        }

        return $status;
    }

    protected function refund(float $amount):enumPaymentStatus
    {
        return enumPaymentStatus::Failed();
    }

    protected function generateTransactionId():string
    {
        $result = mt_rand(1, 9);
        for ($i = 1; $i <= 9; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    private function sign():string
    {
        $data = [
            'mid' => $this->settings->merchantId,
            'txid' => $this->transactionId,
            'type' => self::TYPE_PAYMENT,
            'amount' => $this->amount * 100,
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

    private function readResponse($response):array
    {
        $result = explode("\n", $response);

        return [
            'status' => strtoupper(trim($result[0])),
            'message' => trim($result[2]),
            'authCode' => trim($result[3]),
        ];
    }
}