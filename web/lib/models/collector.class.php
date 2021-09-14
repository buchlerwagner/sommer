<?php
class collector extends ancestor {
	protected $container = [];

	/**
	 * @param $class
	 * @param array $params
	 * @return object
	 * @throws ReflectionException
	 */
	private function createInstance($class, array $params):ancestor {
		$reflectionClass = new ReflectionClass($class);
		return $reflectionClass->newInstanceArgs($params);
	}

	public function add($name, ancestor $obj) {
		$this->container[$name] = $obj;
		$obj->name = $name;
		$obj->setOwner($this);
	}

	public function addByClassName($class, $name = false, $params = [], $forceReload = false) {
		if (empty($name)) $name = $class;
		if (empty($this->container[$name]) || $forceReload) {
		    try {
                $obj = $this->createInstance($class, $params);
                $this->add($name, $obj);
            }catch (ReflectionException $e){
                die('Could not create instance: ' . $e->getMessage());
            }
		}
		return $this->container[$name];
	}

	public function get($name) {
		if ( isset($this->container[$name]) ) {
			return $this->container[$name];
		}else{
			return false;
		}
	}

	public function remove($name) {
		unset($this->container[$name]);
	}
}