<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;
$data = [];

$listName = $this->db->escapeString(trim($this->params[1]));
$query = $this->db->escapeString(trim($_REQUEST['q']));
$action = $this->db->escapeString(trim($_REQUEST['action']));
if(!$action) {
	if(!Empty($this->params[2])){
		$action = $this->db->escapeString(trim($this->params[2]));
	}else {
		$action = 'list';
	}
}

$id = (int) $this->db->escapeString($_REQUEST['id']);

$tables = [
    'events' => [
        'id' => 'f_id',
        'list' => 'getEventsForInvoice',
        'scope' => [
            'f_id' => 'id',
            'e_id' => $_REQUEST['eventId'],
            'date' => $_REQUEST['date'],
        ]
    ],
    'fleet' => [
        'id' => 'ug_id',
        'list' => 'getCars',
        'json' => true,
        'scope' => [
            'ug_id' => 'id',
        ]
    ]
];

if($tables[$listName]){
	$tableName = $listName;
	if(!Empty($tables[$listName]['table'])){
		$tableName = $tables[$listName]['table'];
	}

	switch($action){
		case 'add':
			if(!Empty($tables[$listName]['insert'])) {
				$checkValue = '';
				$valueField = '';
				$insertData = [];

				foreach ($tables[$listName]['insert'] AS $fld => $val) {
					if(isset($_REQUEST[$val])) {
						$insertData[$fld] = $this->db->escapeString(trim($_REQUEST[$val]));

						if ($val === 'value') {
							$valueField = $fld;
							$checkValue = $insertData[$fld];
						}
					}
				}

				if (!Empty($insertData)) {
					$sql = "SELECT " . $tables[$listName]['id'] . " AS id FROM " . DB_NAME_WEB . "." . $tableName . " WHERE " . $valueField . " = '" . $checkValue . "'";
					$row = $this->db->getFirstRow($sql);
					if ($row) {
						$id = $row['id'];
					} else {
						$this->db->sqlQuery(
							$this->db->genSQLInsert(
								DB_NAME_WEB . '.' . $listName,
								$insertData
							)
						);

						$id = $this->db->getInsertRecordId();
					}

					$data['id'] = $id;
				}
			}

			// no break as new values will be incremented as well
		case 'count':
			if(isset($tables[$listName]['counter'])) {
				$this->db->sqlQuery(
					$this->db->genSQLUpdate(
						DB_NAME_WEB . '.' . $tableName,
						[
							$tables[$listName]['counter'] => 'INCREMENT'
						],
						[
							$tables[$listName]['id'] => $id
						]
					)
				);
			}
			break;

		case 'custom':
			$params = [];

			if($tables[$listName]['scope']){
				foreach($tables[$listName]['scope'] AS $field => $rq){
					if(isset($_REQUEST[$rq])) {
						$params[$field] = $this->db->escapeString(trim($_REQUEST[$rq]));
					}else{
						$params[$field] = $rq;
					}
				}
			}

			$method = $tables[$listName]['list'];
			if(method_exists($this->lists, $method)) {
                $this->lists->reset();

			    if($tables[$listName]['json']){
                    $this->lists->setJson();
                }

                $data = $this->lists->$method($params);
            }

			break;

		case 'list':
		default:
			$whereOr = [];
			$whereAnd = [];
			$orderBy = '';

			if(!is_array($tables[$listName]['search'])){
				$tables[$listName]['search'] = [ $tables[$listName]['search'] ];
			}

			$fields = array_values($tables[$listName]['search']);
			if($tables[$listName]['fill']){
				if(!is_array($fields)) $fields = [];
				$fields = array_merge($fields, array_values($tables[$listName]['fill']));
			}

			$sql = "SELECT " . $tables[$listName]['id'] . " AS id, " . ($tables[$listName]['text'] ? $tables[$listName]['text'] : $tables[$listName]['search'][0]) . " AS text" . (!Empty($fields) ? ', ' . implode(', ', $fields) : '') . " FROM " . DB_NAME_WEB . "." . $tableName;
			if($query) {
				foreach($tables[$listName]['search'] AS $field){
					$whereOr[] =  $field . " LIKE '%" . $query . "%'";
				}

				$orderBy .= " ORDER BY text";
				if($tables[$listName]['counter']) {
					$orderBy .= ", " . $tables[$listName]['counter'] . " DESC";
				}
			}else{
				if($tables[$listName]['counter']) {
					$orderBy .= " ORDER BY " . $tables[$listName]['counter'] . " DESC LIMIT 10";
				}else{
					$orderBy .= " ORDER BY " . $tables[$listName]['search'][0] . " DESC LIMIT 10";
				}
			}

			if($tables[$listName]['scope']){
				foreach($tables[$listName]['scope'] AS $field => $rq){
					if(isset($_REQUEST[$rq])) {
						$whereAnd[] = $field . " = '" . $this->db->escapeString(trim($_REQUEST[$rq])) . "'";
					}
				}
			}

			if($tables[$listName]['exclude']) {
				foreach ($tables[$listName]['exclude'] AS $query){
					$whereAnd[] = $query;
				}
			}

			if($whereAnd || $whereOr){
				$where = [];

				if($whereOr){
					$where[] = "(" . implode(' OR ', $whereOr) . ")";
				}

				if($whereAnd){
					$where[] = implode(' AND ', $whereAnd);
				}

				$sql .= " WHERE " . implode(' AND ', $where);
			}

			$res = $this->db->getRows($sql . $orderBy);
			if (!empty($res)) {
				$i = 0;
				foreach ($res as $row) {
					$data['results'][$i] = [
						'id' => $row['id'],
						'text' => $row['text']
					];
					if($tables[$listName]['fill']){
						foreach($tables[$listName]['fill'] AS $field => $value) {
							$data['results'][$i]['fill'][$field] = $row[$value];
						}
					}
					$i++;
				}
			}
			break;
	}
}

$this->data = $data;

