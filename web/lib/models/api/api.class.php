<?php
use Respect\Rest\Router;

class api extends model {
    /**
     * @var Router
     */
    private $router;

	/**
	 * @var mysql
	 */
	public $db;

    /**
     * @var mp_memcache
     */
    public $mem;

    /**
     * @var logger
     */
    public $log;

    /**
     * @var authService
     */
    public $auth;

    private $headers = [];
    private $version = API_CURRENT_VERSION;
    private $language = API_DEFAULT_LANGUAGE;

    private $messageId;
    private $serviceName = false;
    private $actionName;

    protected $services = [];

	public function __construct($db = null){
		parent::__construct();

        $this->headers = [
            'request'  => getallheaders(),
            'response' => [],
        ];

        if(Empty($this->headers['request']['Version'])){
            $this->version = API_CURRENT_VERSION;
        }else{
            $version = $this->headers['request']['Version'];
            if(!in_array($version, $GLOBALS['API_VALID_VERSIONS'])){
                $this->version = API_CURRENT_VERSION;
            }else{
                $this->version = $version;
            }
        }

		try {
            if(!$db) {
                $this->db = db::factory(DB_TYPE, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_WEB, DB_ENCODING);
                $this->db->connect();
            }else{
                $this->db = $db;
            }
		} catch (Exception $e){
			exit('Could not connect to database.');
		}

        if (class_exists('Memcache')) {
            $this->mem = new mp_memcache(MEMCACHE_HOST, MEMCACHE_PORT, MEMCACHE_COMPRESS);
        } else if (class_exists('Memcached')) {
            $this->mem = new mp_memcached(MEMCACHE_HOST, MEMCACHE_PORT);
        }

        $this->log = $this->addByClassName('logger');
        $this->auth = $this->addByClassName('authService');
        $this->addByClassName('translate');

        if(isset($_REQUEST['lang']) && !Empty($_REQUEST['lang'])){
            $this->setLanguage($_REQUEST['lang']);
        }
	}

    /**
     * setup routings
     */
    public function start(){
        $this->router = new Router((API_HOST_NAME ? null : '/api'));
        $this->createMessageId();

        if($this->serviceName = $this->isValidService()) {
            $service = $this->getService();

            $route = $this->router->any('/' . $this->serviceName . '/**', $this->addService($this->serviceName))
                ->accept(array(
                    'application/json' => function ($data) {
                        return $this->sendOutput($data, 'json');
                    }
                ))
                ->through(function () {
                    // Log request data
                    $this->log->saveRequest();
                });

            if(($service['auth'] & API_AUTH_TYPE_BASIC) == API_AUTH_TYPE_BASIC){
                $route->authBasic('Access restricted', function ($user, $pass) {
                    // Basic authentication
                    return $this->auth->authenticate($user, $pass);
                });
            }

            if(($service['auth'] & API_AUTH_TYPE_TOKEN) == API_AUTH_TYPE_TOKEN){
                $route->by(function () {
                    // Check access Token
                    $this->auth->checkToken();
                });
            }

            if(($service['auth'] & API_AUTH_TYPE_APIKEY) == API_AUTH_TYPE_APIKEY){
                $route->by(function () {
                    // Check access API Key
                    $this->auth->checkApiKey($this->getRequestHeader('Api-Key'));
                });
            }
        }

        $this->handleExceptions();

    }

    private function handleExceptions(){
        $this->router->exceptionRoute('Exception', function (apiException $e) {
            $response = $this->setPayloadHeader($e->getData());

            $response['error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];

            if (defined('API_LOG_EXCEPTIONS') && API_LOG_EXCEPTIONS) {
                $this->log->saveRequest();
                $this->log->saveResponse(print_r($response, true), 'txt');
            }

            $e->sendHttpResponseCode();
            $this->auth->closeSession();

            return $this->encodeOutput($response);
        });
    }

    private function isValidService(){
        if (!empty($_REQUEST['path'])) {
            $path = explode('/', trim($_REQUEST['path'], '/'));
            if(strtolower($path[0]) === 'api'){
                array_shift($path);
            }

            $serviceName = strtolower($path[0]);

            return (isset($GLOBALS['API_SERVICES'][$serviceName]) ? $serviceName : false);
        }

        return false;
    }

    private function getService(){
        if($this->serviceName){
            return $GLOBALS['API_SERVICES'][$this->serviceName][$this->getVersion()];
        }

        return [];
    }

    private function addService($service){
        if($name = $GLOBALS['API_SERVICES'][$service][$this->version]['class']) {
            if (!$this->services[$name]) {
                $this->services[$name] = $this->addByClassName($name);
            }

            return $this->services[$name];
        }else{
            return null;
        }
    }

    private function setLanguage($lang){
        $lang = strtolower(trim($lang));

        if(in_array($lang, $GLOBALS['API_LANGUAGES'])){
            $this->language = $lang;
        }else{
            $this->language = API_DEFAULT_LANGUAGE;
        }
    }

    private function encodeOutput($data, $encoding = false){
        if(!$encoding){
            $encoding = $this->getAcceptedEncoding();
        }

        switch($encoding){
            case 'html':
                break;
            case 'json':
            default:
                $data = json_encode($data);
                break;
        }
        return $data;
    }

    private function getAcceptedEncoding(){
        $accept = $this->getRequestHeader('Accept');
        switch ($accept){
            case 'text/html':
                $encoding = 'html';
                break;
            case 'application/json':
            default:
                $encoding = 'json';
                break;
        }

        return $encoding;
    }

    public function getRequestHeader($header = false){
        return ($header ? ($this->headers['request'][$header] ?: false) : $this->headers['request']);
    }

    public function getResponseHeader($header = false){
        return ($header ? ($this->headers['response'][$header] ?: false) : $this->headers['response']);
    }

    private function setResponseHeader($header, $value){
        $this->headers['response'][$header] = $value;
    }

    private function createMessageId(){
        if($this->headers['request']['Message-Id']) {
            $this->messageId = $this->headers['request']['Message-Id'];
        }else {
            $this->messageId = uuid::v4();
        }
    }

    public function getMessageId(){
        return $this->messageId;
    }

    public function getVersion(){
        return $this->version;
    }

    public function getServiceName(){
        return $this->serviceName;
    }

    public function getActionName(){
        return $this->actionName;
    }

    public function setActionName($action){
        $this->actionName = $action;
    }

    public function getClientIP() {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        return $ip;
    }

    private function sendOutput($data, $encoding = 'json'){
        if(!$this->getResponseHeader('Cache-Control')) {
            $this->setResponseHeader('Last-Modified', 	gmdate('D, d M Y H:i:s T'));
            $this->setResponseHeader('Expires', 		gmdate('D, d M Y H:i:s T'));
            $this->setResponseHeader('Pragma', 			'no-cache');
            $this->setResponseHeader('Cache-Control', 	'no-cache, must-revalidate');
        }

        $out = $this->setPayloadHeader();
        $out['data'] = ($data ?: []);

        $this->sendHeaders();
        $output = $this->encodeOutput($out, $encoding);
        $this->log->saveResponse($output, $encoding);
        $this->auth->closeSession();

        return $output;
    }

    private function setPayloadHeader($data = []){
        $out = [
            'messageId' => $this->getMessageId(),
            'version' => $this->version,
            'serverDate' => date(DATE_ATOM),
            'server' => SERVER_ID,
            'clientIp' => $this->getClientIP(),
            'error' => [],
            'data' => [],
        ];

        if($data){
            foreach($data AS $key => $value){
                if(!isset($out[$key])){
                    $out[$key] = $value;
                }
            }
        }

        return $out;
    }

    private function sendHeaders(){
        $headers = $this->getResponseHeader();
        if($headers){
            foreach($headers AS $header => $value){
                header($header . ': ' . $value);
            }
        }
    }

}