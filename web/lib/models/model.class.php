<?php
class model extends collector {
	protected $customModels = [];
	protected $extendPage = [];

	public function __construct(){
		parent::__construct();
	}

    /**
     * @param string $type
     * @param string $name
     * @return bool
     */
	public function loadModel($type, $name) {
		$result = false;

		if(!Empty($this->customModels[$type][$name])) {
			$name = $this->customModels[$type][$name];
		}

		if (file_exists(APP_ROOT . 'model/' . $type . '/' . $name . '.php')) {
			include_once APP_ROOT . 'model/' . $type . '/' . $name . '.php';
			$result = true;
		}

		if($type == 'pages' && !Empty($this->extendPage['all'])){
			include_once APP_ROOT . 'model/' . $type . '/' . $this->extendPage['all'] . '.php';
		}

		if($type == 'pages' && !Empty($this->extendPage[$name])){
			include_once  APP_ROOT . 'model/' . $type . '/' . $this->extendPage[$name] . '.php';
		}

		return $result;
	}
}
