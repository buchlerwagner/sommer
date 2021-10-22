<?php
class apiException extends \Exception  {
	private $httpCode = API_HTTP_OK;
	private $data = [];

	public function __construct($message, $code, $httpCode, $data = [], Exception $previous = null) {
		$this->httpCode = $httpCode;
		$this->data = $data;

		parent::__construct($message, $code, $previous);
	}

	public function getHttpCode(){
		return $this->httpCode;
	}

    public function getData(){
        return $this->data;
    }

	public function sendHttpResponseCode(){
		http_response_code($this->httpCode);
	}
}