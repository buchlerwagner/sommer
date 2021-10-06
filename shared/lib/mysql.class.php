<?php
class mysql extends db {
    public function myConnect() {
        $this->conn = new mysqli($this->hostname, $this->user, $this->pass, $this->database);
        if ( $this->conn->connect_error ) {
            $this->conn = NULL;
            throw new Exception( __METHOD__.": Error connecting to database host.");
        }
        $this->conn->set_charset($this->encoding);
    }

    /** Disconnects persistent database connection */
    public function myDisconnect() {
        if($this->conn) $this->conn->close();
    }

    public function mySqlQuery($query) {
        if (!$this->conn->query($query)) {
            throw new Exception( __METHOD__.": Error executing query: $query. Error message: " . $this->conn->error );
        }
    }

    /**
     * Opens a query and returns resultset
     * @param $query string Query to be opened
     * @return int
     * @throws
     */
    public function myOpenQuery($query) {
        if ( $this->conn->real_query($query) ) {
            $result = $this->conn->store_result();
        } else {
            throw new Exception( __METHOD__.": Error opening query: $query. Error message: ".$this->conn->error );
        }
        return $result;
    }


    /**
     * @param $result mysqli_result
     */
    public function closeQuery($result) {
        $result->free();
    }

    /**
     * Returns the record count of a resultset
     * @param $result mysqli_result
     * @return int
     */
    public function getRecordCount($result) {
        return $result->num_rows;
    }

    /**
     * Returns the next row of the query
     * in an associative array indexed by field names
     * @param $result mysqli_result
     * @return array
     */
    public function getNext($result) {
        return $result->fetch_assoc();
    }

    /**
     * Get last insert ID
     * @return int
     */
    public function getInsertRecordId() {
        return $this->conn->insert_id;
    }

    /**
     * Get number of affected rows after update/delete
     * @return int
     */
    public function getAffectedRows() {
        return $this->conn->affected_rows;
    }

    /**
     * Get last MySQL error
     * @return string
     */
    public function getError() {
        return $this->conn->error;
    }

    /**
     * Escape string
     * @param string $string
     * @return string
     */
    public function escapeString($string){
        $this->connect();
        if (is_array($string)) {
            $string = serialize($string);
        }
        return $this->conn->real_escape_string($string);
    }

    /**
     * Generate SQL SELECT query
     * @param string $tableName
     * @param array $fields
     * @param array $where array of fields or raw where statement
     * @param array $joins table name as key, possible values, string:as, array:keyFields
     * @param bool $groupBy array of fields or string
     * @param bool $orderBy array of fields or string
     * @param bool $limit number of fetched rows
     * @return string
     */
    public function genSQLSelect($tableName, array $fields = [], $where = [], array $joins = [], $groupBy = false, $orderBy = false, $limit = false) {
        if(Empty($fields)){
            $fields = "*";
        }else{
            $fields = implode(', ', $fields);
        }
        $result = "SELECT " . $fields . " FROM " . $this->prepareTableName($tableName);
        if($joins){
            foreach($joins AS $table => $join){
                $result .= " LEFT JOIN " . $this->prepareTableName($table) . ($join['as'] ? ' AS ' . $join['as'] : '');
                if($join['on']){
                    $j = [];
                    foreach ($join['on'] as $key => $val) {
                        $j[] = $key . '=' . $val;
                    }
                    $result .= " ON (" . implode(' AND ', $j) . ")";
                }
            }
        }

        if($where){
            $result .= $this->genSQLWhere($where);
        }

        if($groupBy){
            if(!is_array($groupBy)){
                $groupBy = [$groupBy];
            }
            $result .= " GROUP BY " . implode(', ', $groupBy);
        }

        if($orderBy){
            if(!is_array($orderBy)){
                $orderBy = [$orderBy];
            }
            $result .= " ORDER BY " . implode(', ', $orderBy);
        }

        if($limit){
            $result .= " LIMIT " . $limit;
        }

        return $result;
    }

    /**
     * Generate SQL INSERT query
     * @param string $tablename
     * @param array $insertvalues key/value array of fields/values
     * @param mixed $keyfields array of key fields
     * @param mixed $insertIgnore  array of unique fields
     * @return string
     */
    public function genSQLInsert($tablename, Array $insertvalues, $keyfields = null, $insertIgnore = false) {
        $insertvalues = $this->prepareValues($insertvalues);
        $result = 'INSERT ' . ($insertIgnore ? 'IGNORE ' : '') . 'INTO ' . $this->prepareTableName($tablename) . ' (' . implode(', ', array_keys($insertvalues)) . ') VALUES (' . implode(', ', array_values($insertvalues)) . ')';
        if (is_array($keyfields) && !empty($keyfields)) {
            $result .= " ON DUPLICATE KEY UPDATE ";
            $prefix = '';
            foreach ($insertvalues as $key => $val) {
                if (!in_array($key, $keyfields)) {
                    $result .= $prefix.$key.'='.$val;
                    $prefix=', ';
                }
            }
        }
        return $result;
    }

    /**
     * Generate SQL UPDATE query
     * @param string $tablename
     * @param array $updatevalues key/value array of fields/values
     * @param array $keyfields array of key fields
     * @return string
     */
    public function genSQLUpdate($tablename, Array $updatevalues, Array $keyfields) {
        $updatevalues = $this->prepareValues($updatevalues);
        $result = "UPDATE " . $this->prepareTableName($tablename) . " SET ";
        $set = Array();
        foreach($updatevalues as $key => $val) {
            $set[] = $key . '=' . $val;
        }
        $result .= implode(", ", $set);
        $result .= $this->genSQLWhere($keyfields);
        return $result;
    }

    /**
     * Generate SQL DELETE query
     * @param string $tablename
     * @param array $keyfields key/value array of key fields
     * @return string
     */
    public function genSQLDelete($tablename, Array $keyfields) {
        $result = "DELETE FROM " . $this->prepareTableName($tablename);
        $result .= $this->genSQLWhere($keyfields);
        return $result;
    }

    /**
     * Generate SQL WHERE statement
     * @param string|array $fields key/value pairs with AND concatenation or raw where statement (as string)
     * @return string
     */
    public function genSQLWhere($fields) {
        if(is_array($fields)) {
            $fields = $this->prepareValues($fields, false);

            $where = [];
            foreach ($fields as $key => $val) {
                if(!is_numeric($key)) {
                    if(is_array($val)){
                        if(!Empty($val['in'])){
                            $where[] = $key . ' IN (' . implode(',', $val['in']) . ')';
                        }
                        if(!Empty($val['notin'])){
                            $where[] = $key . ' NOT IN (' . implode(',', $val['notin']) . ')';
                        }
                        if(isset($val['not'])){
                            $where[] = $key . '!="' . $val['not'] . '"';
                        }
                        if(isset($val['is'])){
                            $where[] = $key . ' IS ' . $this->prepareValue($val['is']);
                        }
                        if(isset($val['isnot'])){
                            $where[] = $key . ' IS NOT ' . $this->prepareValue($val['isnot']);
                        }
                        if(isset($val['greater'])){
                            $where[] = $key . '>' . $this->prepareValue($val['greater']);
                        }
                        if(isset($val['less'])){
                            $where[] = $key . '<' . $this->prepareValue($val['less']);
                        }
                    }else {
                        $where[] = $key . '=' . $val;
                    }
                }else{
                    $where[] = $val;
                }
            }
            return ' WHERE ' . implode(" AND ", $where);
        }else{
            return " WHERE " . $fields;
        }
    }

    /**
     * Set SQL variable
     * @param array $vars
     * @return  void
     */
    public function setVariables(array $vars) {
        $sql = [];
        foreach($vars as $key => $val) {
            $sql[] = '@' . $key . '="' . $this->escapeString($val) . '"';
        }
        if (!empty($sql)) {
            $this->sqlQuery("SET " . implode(', ', $sql));
        }
    }

    /**
     * Prepare/escape insert values from the array
     * @param array $values key/value pairs
     * @param bool $serializeValues serialize values in case of array
     * @return array
     */
    private function prepareValues(Array $values, $serializeValues = true) {
        $inc = 0;
        foreach($values as $key => $val) {
            if (is_array($val) && $serializeValues) {
                if(!Empty($val)) {
                    $val = serialize($val);
                }else{
                    $val = '';
                }
            }

            if(!is_array($val)) {
                if (is_numeric(str_replace([' ', ','], ['', '.'], $val)) && substr($val, 0, 1) != 0
                    && stripos($val, 'e') === false && stripos($val, 'f') === false && strlen($val) < 20) {
                    $val = str_replace([' ', ','], ['', '.'], $val);
                } else if ($val === NULL) {
                    $val = 'NULL';
                } else if ($val == 'NOW()') {
                } else if (substr($val, 0, 4) == 'MD5(') {
                } else if (substr($val, 0, 4) == 'AND(') {
                    preg_match('#\((.*?)\)#', $val, $match);
                    $val = $key . " & " . $match[1];
                } else if (substr($val, 0, 3) == 'OR(') {
                    preg_match('#\((.*?)\)#', $val, $match);
                    $val = $key . " | " . $match[1];
                } else if (substr($val, 0, 3) == 'IN(') {
                    preg_match('#\((.*?)\)#', $val, $match);
                    $val = $key . " IN (" . $match[1] . ")";
                    unset($values[$key]);
                    $key = $inc++;
                } else if ($val == 'INCREMENT') {
                    $val = $key . " + 1";
                } else if ($val == 'DECREMENT') {
                    $val = $key . " - 1";
                } else {
                    $val = "'" . $this->escapeString($val) . "'";
                }
            }
            $values[$key] = $val;
        }
        return $values;
    }

    private function prepareValue($value){
        if(!is_array($value)) {
            if (is_numeric(str_replace([' ', ','], ['', '.'], $value)) && substr($value, 0, 1) != 0
                && stripos($value, 'e') === false && stripos($value, 'f') === false && strlen($value) < 20) {
                $value = str_replace([' ', ','], ['', '.'], $value);
            } else if ($value === NULL || $value === 'NULL') {
                $value = 'NULL';
            } else {
                $value = "'" . $this->escapeString($value) . "'";
            }
        }
        return $value;
    }

    /**
     * Prepend database name before the table name
     * @param string $tableName
     * @param bool $prependDatabaseName
     * @return string
     */
    public function prepareTableName($tableName, $prependDatabaseName = true){
        if($this->prependDatabaseName && $prependDatabaseName){
            if(strpos($tableName, $this->database . '.') === false){
                $tableName = $this->database . '.' . $tableName;
            }
        }

        return $tableName;
    }
}
