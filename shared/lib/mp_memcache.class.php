<?php
class mp_memcache {
	/**
	 * @var Memcache
	 */
	protected $conn;

	protected $host;
	protected $port;
	protected $compress;

	public function __construct($host, $port, $compress = false) {
		$this->host = $host;
		$this->port = $port;
		$this->compress = ($compress==false) ? false : MEMCACHE_COMPRESSED;
	}

	public function __destruct() {
		$this->disconnect();
	}

	public function connect() {
		if ( empty($this->conn) ) {
			$this->conn = new Memcache();
			$this->conn->connect($this->host, $this->port);
		}
	}

	public function disconnect() {
		if ( !empty($this->conn) ) {
			$this->conn->close();
			$this->conn = null;
		}
	}

	public function add($key, $data, $expire = 0) {
		$this->connect();
		return $this->conn->add($key, $data, $this->compress, $expire);
	}

	public function set($key, $data, $expire = 0) {
		$this->connect();
		return $this->conn->set($key, $data, $this->compress, $expire);
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
