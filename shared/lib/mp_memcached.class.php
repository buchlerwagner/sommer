<?php
class mp_memcached {
	/**
	 * @var Memcached
	 */
	protected $conn;

	protected $host;
	protected $port;

	public function __construct($host, $port) {
		$this->host = $host;
		$this->port = $port;
	}

	public function __destruct() {
		$this->disconnect();
	}

	public function connect() {
		if ( empty($this->conn) ) {
			$this->conn = new Memcached();
			$this->conn->addServer($this->host, $this->port);
		}
	}

	public function disconnect() {
		if ( !empty($this->conn) ) {
			$this->conn->quit();
			$this->conn = null;
		}
	}

	public function add($key, $data, $expire = 0) {
		$this->connect();
		return $this->conn->add($key, $data, $expire);
	}

	public function set($key, $data, $expire = 0) {
		$this->connect();
		return $this->conn->set($key, $data, $expire);
	}

	public function get($key) {
		$this->connect();
		return $this->conn->get($key);
	}

	public function increment($key, $value = false) {
		$this->connect();
		return $this->conn->increment($key, $value);
	}

	public function delete($key) {
		$this->connect();
		return $this->conn->delete($key);
	}

}
