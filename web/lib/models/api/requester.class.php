<?php
use Respect\Rest\Routable;

abstract class requester extends model implements Routable  {
    const CALL_METHOD_GET       = 'get';
    const CALL_METHOD_POST      = 'post';
    const CALL_METHOD_PUT       = 'put';
    const CALL_METHOD_DELETE    = 'delete';
    const CALL_METHOD_PATCH     = 'patch';

	private $requestBody = null;
	private $method = null;

    abstract public function init():void;

    /**
     * Method: GET
     * @param bool|array $id
     * @return array
     * @throws apiException
     */
    public function get($id = false):array{
        return $this->callAction($id, self::CALL_METHOD_GET);
    }

	/**
     * Method: PUT
	 * @param bool|array $id
     * @return array
	 * @throws apiException
	 */
    public function post($id = false):array{
		$this->processRequestBody();
        return $this->callAction($id, self::CALL_METHOD_POST);
	}

    /**
     * Method: PUT
     * @param bool|array $id
     * @return array
     * @throws apiException
     */
    public function put($id = false):array{
        return $this->callAction($id, self::CALL_METHOD_PUT);
    }

    /**
     * Method: PATCH
     * @param bool|array $id
     * @return array
     * @throws apiException
     */
    public function patch($id = false):array{
        return $this->callAction($id, self::CALL_METHOD_PATCH);
    }

    /**
     * Method: DELETE
     * @param bool|array $id
     * @return array
     * @throws apiException
     */
    public function delete($id = false):array{
        return $this->callAction($id, self::CALL_METHOD_DELETE);
    }

    public function get_ListActions($id):array{
        return $this->getServiceActions(self::CALL_METHOD_GET);
    }

    public function post_ListActions($id):array{
        return $this->getServiceActions(self::CALL_METHOD_POST);
    }

    public function put_ListActions($id):array{
        return $this->getServiceActions(self::CALL_METHOD_PUT);
    }

    public function patch_ListActions($id):array{
        return $this->getServiceActions(self::CALL_METHOD_PATCH);
    }

    public function delete_ListActions($id):array{
        return $this->getServiceActions(self::CALL_METHOD_DELETE);
    }

    /**
     * Call service action
     * @param bool|array $id
     * @param string $method
     * @return array
     * @throws apiException
     */
    private function callAction($id, string $method):array{
        if($action = $this->isActionExists($id[0], $method)){
            if(is_array($id)) {
                array_shift($id);
            }
            $this->init();

            $this->owner->setActionName($action);
            return $this->{$this->method . '_' . $action}($id);
        }else{
            $this->owner->setActionName('notFound');
            throw new apiException('Service not found', 401, API_HTTP_BAD_REQUEST);
        }
    }

    protected function getMethod():string{
        return $this->method;
    }

    protected function getRequestBody(){
        return $this->requestBody;
    }

    protected function getRequestParameters($paramsName = false){
        if($paramsName){
            return $_REQUEST[$paramsName] ?? false;
        }
        return $_REQUEST;
    }

	/**
     * @return void
	 * @throws apiException
	 */
	private function processRequestBody():void{
		$input = file_get_contents('php://input');

		$contentType = $this->owner->getRequestHeader('Content-Type');
		if(strpos($contentType, ';')){
			$contentType = strstr($contentType, ';', true);
		}

		switch($contentType){
			case 'application/json':
				$this->requestBody = json_decode($input, true);
				break;
			case 'multipart/form-data':
				$this->requestBody = $_POST;
				break;
			case 'text/plain':
			case 'text/html':
				$this->requestBody = $input;
				break;
			default:
				throw new apiException('Unable to process request body. Content-Type missing from request header.', 10, API_HTTP_UNSUPPORTED_MEDIA);
		}
	}

    /**
     * @param string $action
     * @return string
     */
    private function getActionNameByUrl(string $action):string{
        $action = strtolower(trim($action));
        $action = preg_replace('/[^0-9a-zA-Z-]/', '', $action);
        $parts = explode('-', $action);
        $parts = array_map('ucfirst', $parts);

        return implode('', $parts);
    }

    /**
     * @param string $action
     * @param $method
     * @return false|string
     */
    private function isActionExists(string $action, $method){
        $action = $this->getActionNameByUrl($action);
        $this->method = $method;

        if(method_exists($this, $this->method . '_' . $action)){
            return $action;
        }

        return false;
    }

    /**
     * List callable actions in the given service
     * @param string $method
     * @return array
     */
    private function getServiceActions(string $method):array{
        $actions = [];
        $methods = get_class_methods($this);

        $method .= '_';

        $len = strlen($method);
        foreach($methods AS $action){
            if((substr($action, 0, $len) === $method)){
                $key = lcfirst(substr($action, $len));
                $actions[] = strtolower(preg_replace('/(?<!^)([A-Z])/', '-\\1', $key));
            }
        }

        return $actions;
    }
}