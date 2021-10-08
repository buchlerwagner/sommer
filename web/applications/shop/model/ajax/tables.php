<?php
$tableName = $this->params[1];
if($this->params[2]) {
	$keyValues = explode('|', $this->params[2]);
}else{
	$keyValues = false;
}
$action = $_REQUEST['action'];
$params = $_REQUEST['params'];
$alias = $_REQUEST['alias'];
if(!$alias){
	$alias = $tableName;
}

/**
 * @var $this router
 * @var $table table
 */

$table = false;
if ($this->loadModel('tables', $tableName . '.table')) {
	$data = (!empty($keyValues)) ? ['foreignkeys' => $keyValues] : [];
	if(!Empty($_REQUEST['options'])) {
		$data['options'] = $_REQUEST['options'];
	}
	$table = $this->addByClassName($tableName . 'Table', ($alias != $tableName ? $alias : $tableName), [$data]);
}

$this->output = OUTPUT_JSON;
$data = [];

if (!empty($table)) {
	$refreshBody = false;
	$refreshPager = true;
	$refreshTotals = false;
	$refreshCounter = false;
	$refreshButtons = false;

	if  (!empty($action)) {
		switch($action) {
			case 'page':
				if (!empty($params['page'])) {
					$refreshBody = true;
					$refreshCounter = true;
					$table->init('page', $keyValues, $params);
				}
				break;

			case 'reload':
				$refreshTotals = true;
				$refreshPager  = true;
				$refreshBody = true;
				$refreshCounter = true;
				$refreshButtons = true;
				$table->init('refresh', $keyValues, $params);
				break;

			default:
				if (method_exists($table, $action)) {
					$table->init($action, $keyValues, $params);
					$refreshBody = true;
				}
				break;
		}
	}

	$this->view = $this->addByClassName('view');

	if ($refreshBody) {
		$table->loadRows();

		if ($table->tableType == 'div') {
			$data['#table_' . $alias . ' div.tbody'] = $this->view->renderContent($table->bodyTemplate, ['table' => $table]);
		} else {
			$data['#table_' . $alias . ' tbody'] = $this->view->renderContent($table->bodyTemplate, ['table' => $table]);
		}
	}

	if ($refreshPager) {
		$data['.table_' . $alias . '_pager'] = $this->view->renderContent('table_pager', ['table' => $table]);
	}

	if ($refreshCounter) {
		$data['.table_' . $alias . '_counter'] = $this->view->renderContent('table_row_counter', ['table' => $table]);
	}

	if ($refreshTotals) {
		if (!$refreshBody) $table->loadRows();
		$data['#table_' . $alias . ' tfoot'] = $this->view->renderContent('table_totals', ['table' => $table]);
	}

    if ($refreshButtons) {
        $data['.table_' . $alias . '_buttons'] = $this->view->renderContent('table_buttons', ['table' => $table]);
    }

}

$this->data = $data;
