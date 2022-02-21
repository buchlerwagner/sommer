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

			case 'check':
				if (!empty($params['field'])) {
					$refreshPager  = false;
					$table->init('check', $keyValues, $params);
				}
				break;

			case 'mark':
				if (!empty($params['field'])) {
                    $refreshBody = true;
					$refreshPager  = false;
					$table->init('mark', $keyValues, $params);
				}
				break;

			case 'delete':
				$refreshTotals = true;
				$refreshPager  = true;
				$refreshBody = true;
				$refreshCounter = true;
                $refreshButtons = true;
				$table->init('delete', $keyValues, $params);

                array_shift($keyValues);
                $params = [
                    'foreignkeys' => $keyValues
                ];

                $table->reInit($params);

                break;

			case 'undelete':
				$refreshTotals = true;
				$refreshPager  = true;
				$refreshBody = true;
				$refreshCounter = true;
				$table->init('unDelete', $keyValues, $params);
				break;

			case 'copy':
				$refreshTotals = true;
				$refreshPager  = true;
				$refreshBody = true;
				$refreshCounter = true;
				$table->init('copy', $keyValues, $params);
				break;

			case 'reload':
				$refreshTotals = true;
				$refreshPager  = true;
				$refreshBody = true;
				$refreshCounter = true;
				$refreshButtons = true;
				$table->init('refresh', $keyValues, $params);
				break;

            case 'sort':
                $refreshPager  = false;
                $table->init('sort', $keyValues, $params);
                break;

            case 'select-row':
                $refreshPager = false;
                $list = $this->getSession($tableName . '-selections');
                if(!$list) $list = [];

                if(!Empty($params['ids']) && is_array($params['ids'])){
                    foreach($params['ids'] AS $id => $val){
                        if(!in_array($id, $list) && $val){
                            $list[] = $id;
                        }elseif(in_array($id, $list) && !$val){
                            unset($list[array_search($id, $list)]);
                        }
                    }
                }

                $this->setSession($tableName . '-selections', $list);

                if(!Empty($list)) {
                    $data = [
                        'fields' => [
                            '.btn-table-bulk-edit' => [
                                'removeclass' => 'disabled',
                            ],
                            '.table-row-selector-counter' => [
                                'html' => count($list)
                            ]
                        ],
                        'data' => $list,
                    ];
                }else{
                    $data = [
                        'fields' => [
                            '.btn-table-bulk-edit' => [
                                'addclass' => 'disabled',
                            ],
                            '.table-row-selector-counter' => [
                                'html' => 0
                            ]
                        ],
                    ];
                }
                break;

            case 'unselect-row':
                $refreshPager = false;
                $this->setSession($tableName . '-selections', []);

                $data = [
                    'fields' => [
                        '.btn-table-bulk-edit' => [
                            'addclass' => 'disabled',
                        ],
                        '.table-row-selector-counter' => [
                            'html' => 0
                        ]
                    ],
                ];
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

        if(method_exists($table, 'getUpdateFields')){
            $fields = $table->getUpdateFields();
            if($fields){
                foreach ($fields AS $selector => $actions){
                    foreach ($actions AS $action => $value){
                        $data['fields'][$selector][$action] = $value;
                    }
                }
            }
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
