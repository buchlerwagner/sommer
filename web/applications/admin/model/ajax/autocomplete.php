<?php
/**
 * @var $this router
 *
 *  [
 *      'text'
 *      'id'
 *      'groupId'
 *      'groupName'
 *  ]
 */

$this->output = OUTPUT_JSON;
$data = [];

$listName = $this->db->escapeString(trim($this->params[1]));
$query = $this->db->escapeString(trim($_REQUEST['q']));
$params = $_REQUEST['params'];

if(method_exists($this->lists, $listName)){
    $data = $this->lists->setJson()->$listName($query, $params);
}

if(!Empty($_REQUEST['callback'])) {
    $this->output = OUTPUT_RAW;
    $this->data = $_REQUEST['callback'] . '(' . json_encode($data) . ')';
}else{
    $this->data = $data;
}
