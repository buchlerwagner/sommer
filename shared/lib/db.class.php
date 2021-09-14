<?php
abstract class db {
    /**
     * Database connection handle.
     * @var mysqli
     */
    protected $conn;

    /** Database host name */
    protected $hostname;

    /** Database user name */
    protected $user;

    /** Database password */
    protected $pass;

    /** Database name */
    protected $database;

    /** Encoding */
    protected $encoding;

    protected $prependDatabaseName = true;

    public static function factory($type, $hostname, $user, $pass, $database, $encoding) {
        if ($type == 'mysql') {
            $result = new mysql();
        } else {
            throw new Exception (__METHOD__.": Unknown database type. Type=$type");
        }
        $result->init($hostname, $user, $pass, $database, $encoding);
        return $result;
    }

    public function init($hostname, $user, $pass, $database, $encoding) {
        $this->hostname = $hostname;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;
        $this->encoding = $encoding;
    }

    public function __destruct() {
        $this->disconnect();
    }

    abstract protected function myConnect();

    public function connect() {
        if ( empty($this->conn) ) {
            $this->myConnect();
        }
    }

    abstract protected function myDisconnect();

    public function disconnect() {
        if ( !empty($this->conn) ) {
            $this->myDisconnect();
            $this->conn = null;
        }
    }

    abstract protected function mySqlQuery($query);

    /**
     * Executes a query without resultset
     *
     * @param $query string Query to be executed
     * @param $sqlDebug false
     */
    public function sqlQuery($query, $sqlDebug = false) {
        if($sqlDebug){
            dd($query);
        }

        $this->connect();
        try {
            $this->mySqlQuery($query);
        } catch (Exception $e) {
            $this->saveLog($query);
        }
    }

    /**
     * Opens a query and returns resultset.
     *
     * @param $query string Query to be opened
     */
    abstract protected function myOpenQuery($query);

    /**
     * Opens a query and returns with a resultset
     *
     * @param $query string Query to be opened
     * @param $sqlDebug bool
     * @return mysqli_result|bool
     */
    public function openQuery($query, $sqlDebug = false) {
        if($sqlDebug){
            dd($query);
        }

        $this->connect();
        try {
            $ret = $this->myOpenQuery($query);
        } catch (Exception $e) {
            $this->saveLog($query);
            $ret = false;
        }
        return $ret;
    }

    /**
     * Closes a resultset.
     *
     * @param $result mysqli_result Resultset
     */
    abstract public function closeQuery($result);

    /**
     * Returns the record count of a resultset.
     *
     * @param $result mysqli_result Resultset
     */
    abstract public function getRecordCount($result);

    /**
     * Returns the next row of the query in an associative array indexed by field names.
     *
     * @param $result mysqli_result Resultset
     */
    abstract public function getNext($result);

    /**
     * @param $query string
     * @param $sqlDebug bool
     * @return array
     */
    public function getRows($query, $sqlDebug = false) {
        $result = $this->openQuery($query, $sqlDebug);
        $res = [];
        if ($result) {
            while ( $row = $this->getNext($result) ) {
                $res[] = $row;
            }
            $this->closeQuery($result);
        }
        return $res;
    }

    /**
     * @param $query string
     * @param $sqlDebug bool
     * @return array|bool
     */
    public function getFirstRow($query, $sqlDebug = false) {
        $row = false;
        $result = $this->openQuery($query, $sqlDebug);
        if ($result) {
            $row = $this->getNext($result);
            $this->closeQuery($result);
        }
        return $row;
    }

    public function prependDatabaseName(bool $value){
        $this->prependDatabaseName = $value;
    }

    abstract public function escapeString($str);

    abstract public function genSQLInsert($tablename, Array $insertvalues, $keyfields = null, $insertIgnore  = false);

    abstract public function genSQLUpdate($tablename, Array $updatevalues, Array $keyfields);

    abstract public function genSQLSelect($tableName, Array $fields = [], Array $joins = [], Array $where = [], Array $groupBy = [], Array $orderBy = [], $limit = false);

    abstract public function getError();

    protected function saveLog($query) {
        $error = $this->getError();
        if ( !empty($error) && defined('DIR_LOG') ) {
            $serverid = 'x';
            if(defined('SERVER_ID')){
                $serverid = SERVER_ID;
            }

            $file_name   = get_class( $this ) . '_errors_'. $serverid . '.txt';
            $folder_name = DIR_LOG . strtolower( get_class( $this ) ) . '/' . date( 'Ym' ) . '/' . date( 'd' );

            if(!is_dir($folder_name)){
                @mkdir($folder_name, 0777, true);
                @chmod($folder_name, 0777);
            }

            $data  = ' ' . date("H:i:s") . ': ' . $error . "\n";
            $data .= '      SQL: ' . $query . "\n";

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
            @chmod($folder_name . '/' . $file_name, 0666);
        }
    }

}
