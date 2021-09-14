<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;
$this->data = [];

$list = trim($this->params[1]);
$action = $this->params[2];

$tables = [
	'quoteItems' => [
		'table' => 'project_quote_items',
		'id' => 'pqi_id',
		'foreignKey' => 'pqi_pq_id',
		'parent' => 'pqi_parent_id',
		'name' => 'pqi_title',
		'description' => 'pqi_description',
		'order' => 'pqi_order',
	]
];

if($tables[$list]) {
	$id = (int) $this->db->escapeString($_REQUEST['id']);
	$listId = (int) $this->db->escapeString($_REQUEST['listid']);

	switch ($action) {
		case 'add':
			$name = trim($_REQUEST['name']);
			$description = trim($_REQUEST['description']);
			if($name && $listId){
				$this->db->sqlQuery(
					$this->db->genSQLInsert(
						DB_NAME_WEB . '.' . $tables[$list]['table'],
						[
							$tables[$list]['name'] => $name,
							$tables[$list]['description'] => $description,
							$tables[$list]['order'] => 0,
							$tables[$list]['parent'] => 0,
							$tables[$list]['foreignKey'] => $listId,
						]
					)
				);

				$this->data['action'] = 'add';
				$this->data['id'] = $this->db->getInsertRecordId();
			}

			break;

		case 'edit':
			$name = trim($_REQUEST['name']);
			$description = trim($_REQUEST['description']);
			$text = trim($_REQUEST['text']);
			if($name && $listId && $id){
				$this->db->sqlQuery(
					$this->db->genSQLUpdate(
						DB_NAME_WEB . '.' . $tables[$list]['table'],
						[
							$tables[$list]['name'] => $name,
							$tables[$list]['description'] => $description,
						],
						[
							$tables[$list]['id'] => $id,
							$tables[$list]['foreignKey'] => $listId,
						]
					)
				);

				$this->data['action'] = 'update';
				$this->data['id'] = $id;
				$this->data['name'] = $name;
				$this->data['description'] = $description;
			}
			break;

		case 'delete':
			if($listId && $id) {
				$sectionIds = [$id];

				$sql = "SELECT " . $tables[$list]['id'] . " FROM " . DB_NAME_WEB . "." . $tables[$list]['table'] . " WHERE " . $tables[$list]['parent'] . "=" . $id;
				$result = $this->db->getRows($sql);
				if($result){
					foreach($result AS $row){
						$sectionIds[] = $row[$tables[$list]['id']];
					}
				}

				$sql = 'DELETE FROM ' . DB_NAME_WEB . '.' . $tables[$list]['table'] . ' WHERE ' . $tables[$list]['foreignKey'] . '="' . $listId . '" AND (' . $tables[$list]['id'] . '="' . $id . '" OR ' . $tables[$list]['parent'] . '="' . $id . '")';
				$this->db->sqlQuery($sql);

				$this->data['action'] = 'delete';
				$this->data['id'] = $id;
			}
			break;

		case 'sort':
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);
			if(is_array($data['items']) && $data['listid']){
				$listId = (int) $data['listid'];

				$parentOrder = 0;
				$sectionOrder = 0;

				foreach($data['items'] AS $item){
					$order = '';
					list(, $id) = explode('-', $item['id']);
					if($item['parentId']){
						list(, $parentId) = explode('-', $item['parentId']);
						$sectionOrder = (int) $item['order'] + 1;
						$order = str_pad($parentOrder, 3, '0', STR_PAD_LEFT) . '.' . str_pad($sectionOrder, 3, '0', STR_PAD_LEFT);
					}else{
						$parentId = 0;
						$parentOrder = (int) $item['order'] + 1;
						$order = str_pad($parentOrder, 3, '0', STR_PAD_LEFT) . '.000';
					}

					$this->db->sqlQuery(
						$this->db->genSQLUpdate(
							DB_NAME_WEB . '.' . $tables[$list]['table'],
							[
								$tables[$list]['order'] => $order,
								$tables[$list]['parent'] => $parentId,
							],
							[
								$tables[$list]['id'] => $id,
								$tables[$list]['foreignKey'] => $listId,
							]
						)
					);
				}
			}
			break;
	}
}
