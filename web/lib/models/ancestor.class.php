<?php
class ancestor {

	/**
	 * @var $owner router|api
	 */
	public $owner;

	public $name;

	public function __construct(){
	}

	/**
	 * @param ancestor $owner
	 * @return void
	 */
	public function setOwner(ancestor $owner) {
		$this->owner =& $owner;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setSession(string $key, $value){
		$_SESSION[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getSession(string $key){
		if(isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}else{
			return false;
		}
	}

	/**
	 * @param string|bool $key
	 * @return void
	 */
	public function delSession($key = false){
		if($key) {
			unset($_SESSION[$key]);
		}else{
			$_SESSION = [];
		}
	}

}