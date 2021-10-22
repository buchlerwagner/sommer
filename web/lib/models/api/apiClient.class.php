<?php
class apiClient extends ancestor {
    const CALL_METHOD_GET       = 'GET';
    const CALL_METHOD_POST      = 'POST';
    const CALL_METHOD_PUT       = 'PUT';
    const CALL_METHOD_DELETE    = 'DELETE';
    const CALL_METHOD_PATCH     = 'PATCH';

    const LOG_REQUEST           = 'rq';
    const LOG_RESPONSE          = 'rs';

    const CLIENT_TIMEOUT        = 10;

    private $url = '';
    private $endPoint = '';
    private $userName;
    private $password;
    private $headers = [];
    private $timeOut = 0;
    private $messageId;

    private $payload = [];
    private $response = null;

    public function setEndPoint(string $url){
        $this->endPoint = rtrim($url, '/') . '/';
        return $this;
    }

    public function setServiceUrl(string $url, array $params = []){
        $this->url = rtrim($url, '/') . '/' . ($params ? '?' . http_build_query($params) : '');
        return $this;
    }

    public function setApiKey(string $key){
        $this->headers[] = 'Api-Key: ' . $key;
        return $this;
    }

    public function setVersion(string $version){
        $this->headers[] = 'Version: ' . $version;
        return $this;
    }

    public function setMessageId(string $id){
        $this->messageId = $id;
        return $this;
    }

    public function setTimeOut(int $sec){
        $this->timeOut = $sec;
        return $this;
    }

    public function setPayload($data){
        $this->payload = (is_array($data) ? json_encode($data, JSON_PRETTY_PRINT) : $data);
        return $this;
    }

    public function addHeader(string $key, string $value){
        $this->headers[$key] = $value;
        return $this;
    }

    public function setCredentials(string $user, $password){
        $this->userName = $user;
        $this->password = $password;
        return $this;
    }

    protected function callService(string $method = self::CALL_METHOD_GET){
        if(!$this->endPoint){
            throw new Exception('Endpoint is missing', 1);
        }

        $ch = curl_init($this->endPoint . $this->url);
        $action = str_replace('/', '_', $this->url);

        $this->headers[] = 'Content-Type: application/json';
        $this->addMessageIdToHeader();

        if($this->userName && $this->password){
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->userName . ":" . $this->password);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, ($this->timeOut ?: self::CLIENT_TIMEOUT));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if(in_array($method, [self::CALL_METHOD_POST, self::CALL_METHOD_PUT, self::CALL_METHOD_PATCH]) && $this->payload) {
            $this->saveLog($this->payload, $action);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->payload);
        }

        $response = curl_exec($ch);
        $this->saveLog($response, $action, self::LOG_RESPONSE);

        $this->processResponse($response);

        curl_close($ch);

        return $this->getResponse();
    }

    protected function getResponse(){
        return $this->response;
    }

    private function addMessageIdToHeader(){
        if(!$this->messageId){
            $this->messageId = uuid::v4();
        }

        $this->headers[] = 'Message-Id: ' . $this->messageId;
        return $this;
    }

    private function processResponse($result){
        $this->response = json_decode($result, true);
    }

    private function saveLog($data, $action, $method = self::LOG_REQUEST){
        if (defined('DIR_LOG') ) {
            $fileName = (microtime(true) * 10000) . '_' . $action . '_' . $method . '.json';
            $folderName = DIR_LOG . 'api-client/' . strtolower( get_class( $this ) ) . '/' . date( 'Ym' ) . '/' . date( 'd' );

            if(!is_dir($folderName)){
                @mkdir($folderName, 0777, true);
                @chmod($folderName, 0777);
            }

            @file_put_contents( $folderName . '/' . $fileName, $data, FILE_APPEND );
        }
    }
}
