<?php

class PaymentGatewayTest extends PaymentProvider {

    public static function isAvailable(): bool {
        return (SERVER_ID == 'development');
    }

    public static function getName(): string {
        return 'Payment Gateway Test';
    }

    public function hasRefund(): bool
    {
        return true;
    }

    protected function init(): void
    {

    }

    protected function pay():void
    {
        $data = [
            'action' => 'init',
            'trid' => $this->transactionId,
            'shopid' => $this->settings->merchantId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'return_url' => base64_encode($this->settings->urlReturn),
        ];

        $url = $this->settings->urlFrontend . 'pay.php?' . http_build_query($data);
        $this->saveLog($url, 'initPayment');

        header('Location: ' . $url);
        exit();
    }

    protected function check():enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();

        $request = [
            'action' => 'check',
            'trid' => $this->transactionId,
        ];

        $url = $this->settings->urlCallback . 'pay.php?' . http_build_query($request);
        $this->saveLog($url, 'checkPayment');

        $result = $this->sendRequest($url);
        $this->saveLog($result, 'checkPayment', 'rs');

        if (!empty($result)) {
            $status = $this->getStatus($result);
        }

        return $status;
    }

    protected function refund(float $amount, Transaction $transaction):enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();

        $request = [
            'action' => 'void',
            'amount' => $amount,
            'trid' => $this->transactionId,
            'shopid' => $this->settings->merchantId,
        ];

        $url = $this->settings->urlCallback . 'pay.php?' . http_build_query($request);
        $this->saveLog($url, 'voidPayment');

        $result = $this->sendRequest($url);
        $this->saveLog($result, 'voidPayment', 'rs');

        if (!empty($result)) {
            $status = $this->getStatus($result);
        }

        return $status;
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

    private function readResponse($response):array
    {
        return json_decode($response, true);
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
            case 1:
                $status = enumPaymentStatus::OK();
                break;
            case 2:
                $status = enumPaymentStatus::Canceled();
                break;
            case 3:
                $status = enumPaymentStatus::Failed();
                break;
            case 4:
                $status = enumPaymentStatus::Voided();
                break;
            default:
                $status = enumPaymentStatus::Pending();
                break;
        }

        return $status;
    }

    protected function onBeforePayment(): void
    {
        // TODO: Implement onBeforePayment() method.
    }

    protected function onAfterPayment(): void
    {
        // TODO: Implement onAfterPayment() method.
    }

    public function callback($data = []): enumPaymentStatus
    {
        return enumPaymentStatus::OK();
    }

    public function sendCallbackResponse($data = []): void
    {
        // TODO: Implement sendCallbackResponse() method.
    }
}