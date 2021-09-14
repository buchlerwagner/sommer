<?php

class dbmongo7 {
	protected $conn;

	protected $host;
	protected $port;
	protected $user;
	protected $password;
	protected $database;

	public function __construct($host, $port, $user, $password, $database) {
		require_once('/var/www/vendor/autoload.php');
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
	}

	public function __destruct() {
		$this->disconnect();
	}

	public function connect() {
		if ( empty($this->conn) ) {
			$credentials = '';
			if (!empty($this->user) && !empty($this->password)) {
				$credentials = $this->user.':'.$this->password.'@';
			}

			$this->conn = new MongoDB\Client('mongodb://'.$credentials.$this->host.':'.$this->port);
			try {
				$dbs = $this->conn->listDatabases();
			}	catch(MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
				error_log('PHP cannot find a MongoDB server');
				$this->conn = null;
			}
		}
	}

	public function disconnect() {
	}

	public function set($collection, $key, $data, $expire = 0, $keys = [], $database = null) {
		if (empty($database)) $database = $this->database;
		$this->connect();
		$update = [
			'$set' => [
				'_id'    => $key,
				'keys'   => $keys,
				'data'   => $data,
				'expire' => new MongoDB\BSON\UTCDateTime($expire * 1000)
			]
		];
		if (empty($keys)) {
			unset($update['$set']['keys']);
		}
		$this->conn->selectDatabase( $database )->selectCollection( $collection )->updateOne(
			['_id' => $key],
			$update,
			['upsert' => true]
		);
	}

	public function insertMany($collection, $insert, $expire = 0, $database = null) {
		if (empty($database)) $database = $this->database;
		$this->connect();
		if (!empty($insert) && is_array($insert)) {
			foreach ($insert as $key => $val) {
				$insert[$key]['expire'] = new MongoDB\BSON\UTCDateTime($expire * 1000);
			}
			$this->conn->selectDatabase($database)->selectCollection($collection)->insertMany($insert);
		}
	}

	public function updateMany($collection, $update, $expire = 0, $database = null) {
		if (empty($database)) $database = $this->database;
		$this->connect();
		$operations = [];
		foreach($update as $key => $val) {
			$operations[]['updateOne'] = [
				['_id' => $val['_id']],
				[
					'$set' => [
						'data'   => $val['data'],
						'expire' => new MongoDB\BSON\UTCDateTime($expire * 1000)
					]
				],
				['upsert' => true]
			];
		}
		if (!empty($operations)) {
			$this->conn->selectDatabase($database)->selectCollection($collection)->bulkWrite($operations);
		}
	}

	public function setBig($key, $data, $expire = 0, $database = null, $encode = true) {
		if (empty($database)) $database = $this->database;
		$this->connect();
		if ($encode) {
			$data = json_encode($data);
			$data = gzdeflate($data, 9);
		}

		$bucket = $this->conn->selectDatabase( $database )->selectGridFSBucket();
		$file = $bucket->findOne(['_id' => $key]);
		if (!empty($file)) {
			$bucket->delete( $file['_id'] );
			$stream = $bucket->openUploadStream($key, ['_id' => $key]);
			fwrite($stream, $data);
			fclose($stream);
		} else {
			$stream = $bucket->openUploadStream($key, ['_id' => $key]);
			fwrite($stream, $data);
			fclose($stream);
		}
	}

	public function get($collection, $key, $database = null) {
		if (empty($database)) $database = $this->database;
		try {
			$this->connect();
			$result = $this->conn->selectDatabase( $database )->selectCollection( $collection )->findOne( ['_id' => $key], ['typeMap' => ['root' => 'array', 'document' => 'array']] );
		} catch (Exception $e) {
			$this->saveLog($key, $collection, $e->getMessage());
			$result['data'] = false;
		}
		return $result['data'];
	}

	public function getBig($key, $database = null, $decode = true) {
		if (empty($database)) $database = $this->database;
		$result = [];
		try {
			$this->connect();
			$bucket = $this->conn->selectDatabase( $database )->selectGridFSBucket();
			// $stream = $bucket->openDownloadStreamByName($key);
			$stream = $bucket->openDownloadStream($key);
			$contents = stream_get_contents($stream);
			if (!empty($contents)) {
				if ($decode) {
					$uncompressed = @gzinflate($contents);
					if (!empty($uncompressed)) $contents = $uncompressed;
					$result = json_decode($contents, true);
				} else {
					$result = $contents;
				}
			}
		} catch (Exception $e) {
			$this->saveLog($key, 'GridFS', $e->getMessage());
		}
		return $result;
	}

	public function getAll($collection, array $criteria, $database = null) {
		if (empty($database)) $database = $this->database;
		$result = [];
		$this->connect();
		$cursor = $this->conn->selectDatabase( $database )->selectCollection( $collection )->find( $criteria, ['typeMap' => ['root' => 'array', 'document' => 'array']] );
		foreach ($cursor as $row) {
			$result[ $row['_id'] ] = $row['data'];
		}
		return $result;
	}

	public function delete($collection, $criteria, $database = null) {
		if (empty($database)) $database = $this->database;
		if (!is_array($criteria)) {
			$criteria = ['_id' => $criteria];
		}
		$this->connect();
		$result = $this->conn->selectDatabase( $database )->selectCollection( $collection )->deleteMany( $criteria );
		return $result->getDeletedCount();
	}

	public function deleteBig($criteria, $database = null) {
		if (empty($database)) $database = $this->database;
		if (!is_array($criteria)) {
			$criteria = ['_id' => $criteria];
		}
		$this->connect();
		$bucket = $this->conn->selectDatabase( $database )->selectGridFSBucket();
		$cursor = $bucket->find( $criteria );
		foreach ($cursor as $row) {
			$bucket->delete( $row['_id'] );
		}
	}

	public function collectGarbage($collection = null, $database = null) {
		if (empty($database)) $database = $this->database;
		$criteria = ['expire' => [ '$lt' => new MongoDB\BSON\UTCDateTime ]];
		if (!empty($collection)) {
			$this->delete($collection, $criteria, $database);
		} else {
			$this->deleteBig($criteria, $database);
		}
	}

	/**
	 * @param string $collection
	 * @param mixed $keys
	 * @param string $database
	 */
	public function createIndex($collection, $keys, $database = null) {
		if (empty($database)) $database = $this->database;
		$this->connect();
		if (!is_array($keys)) {
			$keys = [$keys => 1];
		}
		$this->conn->selectDatabase( $database )->selectCollection( $collection )->createIndex( $keys );
	}

	/**
	 * Drop database
	 * @param null|string $database
	 * @return array
	 */
	public function drop($database = null){
		if (empty($database)) $database = $this->database;
		$this->connect();
		return $this->conn->dropDatabase($database);
	}

	protected function saveLog($key, $collection, $error) {
		if ( !empty($error) && defined('DIR_LOG') ) {
			$serverid = 'x';
			if(defined('SERVER_ID')){
				$serverid = SERVER_ID;
			}

			$file_name   = $collection . '_errors_'. $serverid . '.txt';
			$folder_name = DIR_LOG . strtolower( get_class( $this ) ) . '/' . date( 'Ym' ) . '/' . date( 'd' );

			if(!is_dir($folder_name)){
				@mkdir($folder_name, 0777, true);
				@chmod($folder_name, 0777);
			}

			$data  = ' ' . date("H:i:s") . ': ' . $error . "\n";
			$data .= '      key: ' . $key . "\n";

			$callstack = Array();
			$trace = debug_backtrace();
			foreach($trace as $i => $val) {
				if ($i == 0) continue;
				$func = (!empty($val['class'])) ? $val['class'] . $val['type'] . $val['function'] : $val['function'];
				$callstack[] = str_pad(basename($val['file']), 30, ' ') . ' (' . $val['line'] . ') ' . $func . '()';
			}
			$callstack = array_reverse($callstack);

			foreach($callstack as $i => $val) {
				$data .= ($i == 0) ? 'CallStack: ' : '           ';
				$data .= str_pad($i, 2, ' ', STR_PAD_LEFT) . '. ' . $val . "\n";
			}
			$data .= str_repeat('-', 70) . "\n";


			@file_put_contents( $folder_name . '/' . $file_name, $data, FILE_APPEND );
			chmod($folder_name . '/' . $file_name, 0666);
		}
	}

}
