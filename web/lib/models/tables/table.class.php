<?php
class table extends model {
	public $className;
	public $header = true;
	public $headerCaption = false;
	public $boxed = true;
	public $columns;
	public $buttons = [];
	public $parameters;
	public $rows;
	public $rowGroups = [];
	public $useAssocArray = true;
	public $assocIdField = false;
	public $settings;

	public $dbTable;
	public $keyFields;
	public $foreignKeyFields;
	public $deleteField;
	public $deleteDateField;
	public $showDeletedRecords = false;
	public $orderFields = [];
	public $join;
	public $where;
	public $groupBy;
	public $having;
	public $formName;
	public $viewForm = false;
	public $formTemplate = false;
	public $form;
	public $inlineForm;

	public $baseURL = './';
	public $defaultAction = 'edit';
	public $showRowIndex = false;
	public $noHeader = false;
	public $tableClass = 'table table-hover mb-0';
	public $delete = true;          // set false, to disable the delete option
	public $undelete = true;          // set false, to disable the undelete option
	public $edit = true;          // set false, to disable the edit option
	public $copy = false;          // set false, to disable the copy option
	public $view = false;          // set false, to disable the view option
	public $create = true;
	public $copyChangeFields = [
		'remove' 	=> [],		// list fields which need to be removed
		'replace' 	=> [],		// list field in keys to replace its value
		'add' 		=> [],		// list field in keys to add to its original value
        'callback' 	=> false	// callback method name
	];

	public $tableType = 'table';    // table, datatable, div, inline
    public $sortable = false;
    public $sortField = false;
    public $sortGroupField = false;
	public $multipleSelect = false;
	public $selection = [];
	public $rowClick = true;
	public $customUrl = false;
	public $target = 'new-tab';
	public $simple = false;
	public $readonly = false;
	public $options = true;
	public $optionsWidth = 2;
	public $returnAfterSave = true;
	public $bodyTemplate = 'table_body';
	public $infoRowTemplate = false;
	public $optionTemplate = false;
	public $additionalOptionsTemplate = false;
	public $subTable = false;
	public $modalSize = false;
	public $viewModalSize = false;
	public $pagerOnBottom = true;
	public $buttonsPosition = 'bottom'; // both, top, bottom
	public $hideCounter = false;
	public $summarize = [];
	public $totals = [];
	public $actualRow;
	public $returnUrl = false;
	public $debugSql = false;
	public $includeBefore = false;
	public $includeAfter = false;

    private $fieldsToUpdate = [];

	public function __construct($parameters = []) {
		parent::__construct();
		$this->className = substr(get_class($this), 0, -5);
		$this->parameters = $parameters;
	}

	public function reInit($parameters = []){
        $this->columns = [];
        $this->buttons = [];

        if($parameters){
            $this->parameters = $parameters;
        }

        if (!empty($this->parameters['foreignkeys']) && !empty($this->foreignKeyFields)) {
            $tmp = (!empty($this->settings['foreignkeys'])) ? $this->settings['foreignkeys'] : [];

            foreach($this->foreignKeyFields as $i => $field) {
                if (isset($this->parameters['foreignkeys'][$i])) {
                    $this->settings['foreignkeys'][$field] = $this->parameters['foreignkeys'][$i];
                } else {
                    break;
                }
            }
            if ($tmp != $this->settings['foreignkeys']) {
                $this->settings['page'] = 1;
            }
        }

        $this->setup();
    }

	public function init($action = false, $keyValues = [], $params = []) {
		$this->header = '';
        $tableName = false;
        $redirect = false;
        $hasAction = false;

        $data = [
            'keyfields' => [],
            'backurl' => ''
        ];

		$this->settings = [
			'page'          => 1,
			'display'       => 10,
			'orderfield'    => '',
			'orderdir'      => 'asc',
			'recordcount'   => 0,
			'pagenum'       => 1,
			'filters'       => [],
			'staticfilters' => [],
			'foreignkeys'   => [],
		];

        if ($this->owner->page != 'ajax') {
            $this->settings['rights'] = ((!empty($this->owner->originalPage)) ? $this->owner->originalPage : $this->owner->page);
        }

        if (empty($this->settings['rights']) && $this->owner->user->isLoggedIn()) {
            if ($this->owner->originalPage) $page = $this->owner->originalPage;
            if ($page != 'ajax') {
                $this->settings['rights'] = $page;
            }
        }

        $this->selection = $this->owner->getSession($this->name . '-selections');
        $this->setup();

        $savedSettings = $this->getSession('table_settings_' . $this->name);
		if (!empty($savedSettings)) {
			$this->settings = $savedSettings;
		}

		if ($this->owner->user->isLoggedIn() && $this->owner->user->getUser()['access_rights'][ $this->settings['rights'] ] < ACCESS_RIGHT_WRITE) {
			if(isset($this->settings['custom-role'])) {
				if(!$this->owner->user->hasRole($this->settings['custom-role'])) {
					$this->readonly = true;
					$this->delete = false;
					unset($this->buttons);
				}
			}else {
				$this->readonly = true;
				$this->delete = false;
				unset($this->buttons);
			}
		}

		if (!empty($this->parameters['foreignkeys']) && !empty($this->foreignKeyFields)) {
			$tmp = (!empty($this->settings['foreignkeys'])) ? $this->settings['foreignkeys'] : [];

			foreach($this->foreignKeyFields as $i => $field) {
				if (isset($this->parameters['foreignkeys'][$i])) {
					$this->settings['foreignkeys'][$field] = $this->parameters['foreignkeys'][$i];
				} else {
					break;
				}
			}
			if ($tmp != $this->settings['foreignkeys']) {
				$this->settings['page'] = 1;
			}
		}

        if (is_array($this->owner->params) && count($this->owner->params) > 0 && !$action) {
            if (!$this->subTable) {
                $data['backurl'] = str_repeat('../', count($this->owner->params));
                $urlAction = $this->owner->params[0];
                $tmp = explode('|', $this->owner->params[1]);
                foreach($this->keyFields as $i => $key) {
                    $data['keyfields'][$key] = (isset($tmp[$i])) ? $tmp[$i] : 0;
                }
            } else {
                // searching for table name
                $tmp = $this->owner->params;
                foreach ($tmp as $key => $val) {
                    if ($val == $this->name && (count($tmp) > $key + 2)) {
                        $data['backurl'] = str_repeat('../', (count($tmp) - $key - 1));
                        $urlAction = $tmp[$key + 1];
                        $keyValues = explode('|', $tmp[$key + 2]);
                        foreach ($this->keyFields as $i => $keyField) {
                            $data['keyfields'][$keyField] = (isset($keyValues[$i])) ? $keyValues[$i] : 0;
                        }
                        break;
                    }
                }
            }

            $urlAction = explode('|', $urlAction);
            $action = $urlAction[0];
            $tableName = $urlAction[1];
        }else{
            $hasAction = true;

            if($keyValues && $this->keyFields){
                $tmp = (!empty($this->settings['foreignkeys'])) ? $this->settings['foreignkeys'] : [];
                foreach($this->keyFields as $i => $keyField) {
                    $data['keyfields'][$keyField] = (isset($keyValues[$i])) ? $keyValues[$i] : 0;
                    if(isset($this->settings['foreignkeys'][$keyField]) && !Empty($keyValues[$i])){
                        $this->settings['foreignkeys'][$keyField] = $keyValues[$i];
                    }
                }
                if ($tmp != $this->settings['foreignkeys']) {
                    $this->settings['page'] = 1;
                }
            }
        }

        if (empty($tableName) || $tableName == $this->name) {
            $mainKey = $data['keyfields'][$this->keyFields[0]];

            switch($action) {
                case 'delete':
                    if(!$hasAction) $redirect = true;
                    $this->delete($data['keyfields'], $params);
                    break;

                case 'undelete':
                    if(!$hasAction) $redirect = true;
                    $this->unDelete($data['keyfields'], $params);
                    break;

                case 'copy':
                    if(!$hasAction) $redirect = true;
                    $this->copy($data['keyfields'], $params);
                    break;

                case 'check':
                    $this->check($data['keyfields'], $params);
                    break;

                case 'mark':
                    $this->check($data['keyfields'], $params, true);
                    break;

                case 'page':
                    $this->page($params);
                    break;

                case 'sort':
                    $this->sort($params);
                    break;

                case 'reload':
                    break;

                case 'view':
                    if (!empty($this->formName) && $this->view) {
                        if (!empty($this->settings['foreignkeys'])) {
                            foreach($this->settings['foreignkeys'] as $key => $val) {
                                $data['keyfields'][$key] = $val;
                            }
                        }

                        if(!Empty($this->settings['type'])){
                            $data['type'] = $this->settings['type'];
                        }

                        if(!Empty($this->parameters['options'])){
                            $data['options'] = $this->parameters['options'];
                        }

                        $data['viewOnly'] = true;
                        $data['editUrl'] = $data['backurl'] . 'edit|' . $this->name . '/' . implode('|', $data['keyfields']) . '/';
                        $this->form = $this->owner->loadForm($this->formName, $data, $this->formName . 'Form');
                    }
                    break;

                case 'edit':
                    if (!empty($this->formName) && ($this->edit || ($this->create && !$mainKey))) {
                        if (!empty($this->settings['foreignkeys'])) {
                            foreach($this->settings['foreignkeys'] as $key => $val) {
                                $data['keyfields'][$key] = $val;
                            }
                        }

                        if(!Empty($this->settings['type'])){
                            $data['type'] = $this->settings['type'];
                        }

                        if(!Empty($this->parameters['options'])){
                            $data['options'] = $this->parameters['options'];
                        }

                        $data['viewUrl'] = $data['backurl'] . 'view|' . $this->formName . '/' . implode('|', $data['keyfields']) . '/';
                        $this->form = $this->owner->loadForm($this->formName, $data, $this->formName . 'Form');

                        if ($this->form->state == FORM_STATE_SAVED AND $this->returnAfterSave) {
                            $this->owner->pageRedirect($data['backurl']);
                        }elseif($this->form->state == FORM_STATE_SAVED AND !$this->returnAfterSave){
                            $urlParams = '';
                            if($this->form->urlParams){
                                $urlParams = '?' . http_build_query($this->form->urlParams);
                            }

                            $this->owner->addMessage(router::MESSAGE_SUCCESS, 'LBL_SAVED', 'LBL_DATA_SAVED_SUCCESSFULLY');
                            $this->owner->pageRedirect('../' . implode('|', $this->form->keyFields) . '/' . $urlParams);
                        }
                    }else{
                        $this->owner->pageRedirect($data['backurl']);
                    }
                    break;
                default:
                    if (!empty($data['keyfields']) && !Empty($action)) {
                        if(method_exists($this, $action)){
                            if(!$params) $params = [];
                            call_user_func_array([$this, $action], [$data['keyfields'], $params]);
                        }
                    }
                    break;
            }

            if($redirect){
                $this->owner->pageRedirect(($this->returnUrl ?: $data['backurl']));
            }
        }
	}

	public function __destruct() {
        if(!Empty($this->settings)) {
            $this->setSession('table_settings_' . $this->name, $this->settings);
        }
	}

	public function reset() {
		$this->settings = null;
		$this->delSession('table_settings_' . $this->name);
	}

	public function getWhere($filtered = true) {
		$where = [];
		if (!empty($this->where)) {
			$where[] = $this->where;
		}

		if (!empty($this->deleteField) && !$this->showDeletedRecords) {
			if ($this->settings['show-archived']) {
				$where[] = $this->deleteField . " = 1";
			} else {
				$where[] = $this->deleteField . " != 1";
			}
		}

		if (!empty($this->settings['foreignkeys'])) {
			$foreignWhere = [];
			foreach($this->settings['foreignkeys'] as $keyField => $keyValue) {
				$foreignWhere[] = "$keyField = '" . $this->owner->db->escapeString($keyValue) . "'";
				if (!empty($newRecordWhere)) {
					$newRecordWhere[] = "$keyField = 0";
				}
			}
			if (!empty($newRecordWhere)) {
				$where[] = '((' . implode(' AND ', $foreignWhere) . ') OR (' . implode(' AND ', $newRecordWhere) . '))';
			} else {
				$where = array_merge($where, $foreignWhere);
			}
		}

		if ($filtered) {
			if (!empty($this->settings['filters'])) {
				$where = array_merge($where, $this->settings['filters']);
			}

			if (!empty($this->settings['staticfilters'])) {
				$where = array_merge($where, $this->settings['staticfilters']);
			}
		}

		return ((!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	public function loadRows() {
		if (!empty($this->dbTable)) {
			$select = $this->keyFields;
			foreach($this->columns as $col) {
				if (!empty($col['field'])) $select[] = $col['field'];
			}

			if($this->deleteField){
				$select[] = $this->deleteField;
			}

            if($this->rowGroups){
                foreach ($this->rowGroups AS $col) {
                    if (!empty($col['name']) && !in_array($col['name'], $select)){
                        $select[] = $col['name'];
                        if($col['id']){
                            $select[] = $col['id'];
                        }
                        if($col['description']){
                            $select[] = $col['description'];
                        }
                    }
                }
            }

			$groupBy = (!empty($this->groupBy)) ? ' GROUP BY ' . $this->groupBy : '';
			if (!empty($groupBy) && !empty($this->having)) {
				$groupBy .= " HAVING " . $this->having;
			}

			if($this->join) {
				$this->join = ' ' . $this->join;
			}else{
				$this->join = '';
			}
			$where = $this->getWhere(false);

			$res = $this->owner->db->getFirstRow(
				'SELECT COUNT(*) OVER() as cnt FROM ' . $this->owner->db->prepareTableName($this->dbTable) . $this->join . $where . $groupBy . ' LIMIT 1'
			);
			$this->settings['totalcount'] = (int) $res['cnt'];

			$where = $this->getWhere(true);

			$orderBy = '';
			if (!empty($this->settings['orderfield'])) {
				$orderBy = " ORDER BY " . $this->settings['orderfield'] . ' ' . ((strtolower($this->settings['orderdir']) == 'desc') ? 'desc' : 'asc');
			}
			$limit = '';
			if ($this->settings['display'] > 0) {
				$limit = " LIMIT " . (($this->settings['page'] - 1) * $this->settings['display']) . ', ' . $this->settings['display'];
			}

			$res = $this->owner->db->getRows(
				"SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $select) . " FROM " . $this->owner->db->prepareTableName($this->dbTable) . $this->join . $where . $groupBy . $orderBy . $limit
			);

			if($this->debugSql){
				d($this->settings, $this->name . ' settings');
				dd("SELECT " . implode(', ', $select) . " FROM " . $this->owner->db->prepareTableName($this->dbTable) . $this->join . $where . $groupBy . $orderBy . $limit, $this->name . ' SQL dump');
			}

			if (!empty($res)) {
				$i = 0;
				foreach($res as $dbRow) {
                    $groups = [];
                    if($this->rowGroups){
                        foreach($this->rowGroups AS $col){
                                $idField = $col['id'];
                            $textField = $col['name'];
                            if($col['alias']){
                                $textField = $col['alias'];
                                }

                            $groups[$dbRow[$idField]] = [
                                'id' => $dbRow[$idField],
                                'idKey' => $idField,
                                'text' => $dbRow[$textField],
                                'description' => ($col['description'] ? $dbRow[$col['description']] : false)
                            ];

                            unset($dbRow[$textField]);
                            if($col['description']){
                                unset($dbRow[$col['description']]);
                            }
                        }
                    }

					if($this->useAssocArray) {
						if($this->assocIdField){
                            $key = $dbRow[$this->assocIdField];
                            $rowId = $dbRow[$this->assocIdField];
                        }else {
                            $key = $i;
                            $rowId = $dbRow[$this->keyFields[0]];
						}

                        $this->rows[$key] = $dbRow;
                        $this->rows[$key]['__groupId'] = 0;
                        $this->rows[$key]['__id'] = $rowId;
                        $this->rows[$key]['options']['delete'] = true;
                        $this->rows[$key]['options']['edit'] = true;

                        if($this->deleteField) {
                            $this->rows[$key]['options']['isDeleted'] = $dbRow[$this->deleteField];
                            unset($this->rows[$key][$this->deleteField]);
                        }
                        if($this->rowGroups) {
                            foreach($groups AS $groupId => $group) {
                                $idKey = $group['idKey'];
                                unset($group['idKey']);
                                $this->rows[$key]['__groupId'] = $groupId;
                                $this->rows[$key]['groups'][$groupId] = $group;
                                unset($this->rows[$key][$idKey]);
                            }
                        }
                    }else {
						$deleted = 0;
						if($this->deleteField) {
							$deleted = $dbRow[$this->deleteField];
							unset($dbRow[$this->deleteField]);
						}
						$row = array_values($dbRow);
						$this->rows[$i] = $row;
						if($this->deleteField) {
							$this->rows[$i][-1]['isDeleted'] = $deleted;
						}
                        if($this->rowGroups) {
                            $this->rows[$i][-2] = $groups;

                            foreach($groups AS $groupId => $group) {
                                $this->rows[$i][-2][$groupId] = $group;
                            }
                        }
                        $this->rows[$i][-1]['delete'] = true;
                        $this->rows[$i][-1]['edit'] = true;
                    }
					$i++;
				}
			}
			$res = $this->owner->db->getFirstRow("SELECT FOUND_ROWS() as filteredcnt");
			$this->settings['filteredcount'] = $res['filteredcnt'];

			if (!empty($this->settings['display'])) {
				$this->settings['pagenum'] = ceil($res['filteredcnt'] / $this->settings['display']);
				if ($this->settings['pagenum'] < 1) {
					$this->settings['pagenum'] = 1;
					$this->settings['page'] = 1;
				} else if ($this->settings['page'] > $this->settings['pagenum']) {
					$this->settings['page'] = $this->settings['pagenum'];
				}
			} else {
				$this->settings['pagenum'] = 1;
				$this->settings['page'] = 1;
			}

			if (!empty($this->summarize)) {
				foreach($this->summarize as $rowKey => $sumRow) {
					foreach($sumRow as $sum) {
						if (!empty($sum['field'])) {
							$sql = "SELECT SUM(" . $sum['field'] . ") as sumfield";
							if (!empty($sum['unitfield'])) {
								$sql .= ", " . $sum['unitfield'] . " as unitfield";
							}
							$sql .= " FROM " . $this->dbTable . $where;
							if (!empty($sum['where'])) {
								$sql .= (!empty($where)) ? ' AND ' : ' WHERE ';
								$sql .= $sum['where'];
							}
							if (!empty($sum['unitfield'])) {
								$sql .= " GROUP BY " . $sum['unitfield'] . " ORDER BY unitfield ASC";
							}
							$this->totals[$rowKey][$sum['field']] = $this->owner->db->getRows($sql);
						}
					}
				}
			}

			$this->onAfterLoad();
		}
	}

	private function delete($keyValues, $params = []){
		if (!empty($keyValues) && $this->delete && $this->isDeleteable($keyValues)) {
			if ($this->onBeforeDelete($keyValues, empty($this->deleteField))) {
				if (!empty($this->deleteField)) {
					$updateData = [
						$this->deleteField => 1
					];

					if($this->deleteDateField){
						$updateData[$this->deleteDateField] = 'NOW()';
					}

					$this->owner->db->sqlQuery(
						$this->owner->db->genSQLUpdate($this->dbTable, $updateData, $keyValues)
					);
				} else {
					$tables = explode(' ', $this->dbTable);
					$this->owner->db->sqlQuery(
						$this->owner->db->genSQLDelete($tables[0], $keyValues)
					);
				}
			}
			$this->onAfterDelete($keyValues, empty($this->deleteField));
		}
	}

	private function unDelete($keyValues, $params = []){
		if (!empty($keyValues) && !empty($this->deleteField) && $this->delete) {
			$updateData = [
				$this->deleteField => 0
			];

			if($this->deleteDateField){
				$updateData[$this->deleteDateField] = NULL;
			}

			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate($this->dbTable, $updateData, $keyValues)
			);
		}
	}

	private function copy($keyValues, $params = []){
		if (!empty($keyValues) && $this->copy) {
			$keyField = array_keys($keyValues)[0];

			$sql = "SELECT * FROM " . $this->owner->db->prepareTableName($this->dbTable) . " " . $this->owner->db->genSQLWhere($keyValues);
			$row = $this->owner->db->getFirstRow($sql);
			if($row){
				unset($row[$keyField]); // remove index field

				if($this->copyChangeFields['remove']){
					foreach($this->copyChangeFields['remove'] AS $field){
						unset($row[$field]);
					}
				}
				if($this->copyChangeFields['replace']){
					foreach($this->copyChangeFields['replace'] AS $field => $newValue){
						$row[$field] = $newValue;
					}
				}
				if($this->copyChangeFields['add']){
					foreach($this->copyChangeFields['add'] AS $field => $newValue){
						$row[$field] .= $newValue;
					}
				}

                if($this->copyChangeFields['callback'] && method_exists($this, $this->copyChangeFields['callback'])){
                    $row = $this->{$this->copyChangeFields['callback']}($row);
                }

				$this->owner->db->sqlQuery(
					$this->owner->db->genSqlInsert(
						$this->dbTable,
						$row
					)
				);

				$this->onAfterCopy($keyValues, $this->owner->db->getInsertRecordId());
			}
		}
	}

	private function page($params = []){
		switch($params['page']) {
			case 'prev':
				if ($this->settings['page'] > 1) {
					$this->settings['page'] -= 1;
				}
				break;
			case 'next':
				if ($this->settings['page'] < $this->settings['pagenum']) {
					$this->settings['page'] += 1;
				}
				break;
			default:
				if (is_numeric($params['page']) && $params['page'] >= 1) {
					$this->settings['page'] = (int) $params['page'];
				}
				break;
		}
	}

	private function check($keyValues, $params = [], $mark = false){
		$validField = false;
		$field = $params['field'];
		$value = $params['value'];
        $_keyValues = $keyValues;

		foreach($this->columns as $col) {
			if ($col['field'] == $field) {
				$validField = true;
				break;
			}
		}

		$where = [];
		foreach($_keyValues as $key => $keyValue) {
			$where[] = $key . "='" . $this->owner->db->escapeString($keyValue) . "'";
		}
		$where = implode(' AND ', $where);

		if ($validField && !empty($where)) {
		    if($mark){
                $where2 = [];
                array_shift($_keyValues);
                foreach($_keyValues as $key => $keyValue) {
                    $where2[] = $key . "='" . $this->owner->db->escapeString($keyValue) . "'";
                }
                $where2 = implode(' AND ', $where2);

                $this->owner->db->sqlQuery(
                    "UPDATE " . $this->owner->db->prepareTableName($this->dbTable) . " SET " . $field . "='0' WHERE " . $where2
                );
            }

			$this->owner->db->sqlQuery(
				"UPDATE " . $this->owner->db->prepareTableName($this->dbTable) . " SET " . $field . "='" . $this->owner->db->escapeString($value) . "' WHERE " . $where
			);

			$this->onCheck($keyValues, $field, $value);
		}
	}

    private function sort($params){
        if($params['order'] && is_array($params['order'])){
            $i = 1;
            foreach($params['order'] AS $id){
                if($id) {
                    $where = [
                        $this->keyFields[0] => (int) $id,
                    ];
                    if($this->sortGroupField && $params['groupId']){
                        $where[$this->sortGroupField] = (int) $params['groupId'];
                    }

                    $this->owner->db->sqlQuery(
                        $this->owner->db->genSQLUpdate(
                            $this->dbTable,
                            [
                                $this->sortField => $i,
                            ],
                            $where
                        )
                    );
                    $i++;
                }
            }

            $this->onAfterSort($params);
        }
    }

    public function addColumns(column ...$columns){
	    foreach($columns AS $column){
            $this->addColumn($column);
        }
    }

	public function addColumn(column $column){
        $this->columns[$column->getId()] = $column->getColumn();
    }

	public function onBeforeDelete($keyFields, $real = true) {
		return true;
	}

	public function onAfterDelete($keyFields, $real = true) {
	}

	public function onCheck($keyValues, $field, $value) {
	}

    public function onAfterSort($params) {
    }

	public function onAfterLoad(){
	}

	public function onAfterCopy($keyFields, $newId){
	}

	public function alterRow($row){
		return $row;
	}

	public function isDeleteable($keyFields){
		return true;
	}

	public function addInlineForm($form, $params = []){
		$tmp = explode('|', $this->owner->params[1]);
		foreach($this->foreignKeyFields AS $i => $key) {
			$params['keyvalues'][] = (isset($tmp[$i])) ? $tmp[$i] : 0;
		}

		$this->inlineForm = $this->owner->loadForm($form, $params, $form . 'Form');
	}

	public function addButton($caption, $isModal = false, $params = [], $index = false){
		if($params['url']) {
			$url = $params['url'];
		}else{
			$url = false;
		}

		if($isModal) {
			if(!$url) {
				if($params['form']){
					$form = $params['form'];
				}else{
					$form = $this->formName;
				}
				$url = '/ajax/forms/' . $form . '/0' . ($this->parameters['foreignkeys'][0] ? '|' . $this->parameters['foreignkeys'][0] : '') . '/' . $this->name . '/';
			}
			$data = [
				'target' => '#ajax-modal',
				'toggle' => 'modal'
			];

			if($params['size']){
                $data['size'] = $params['size'];
            }elseif($this->modalSize){
                $data['size'] = $this->modalSize;
            }
		}else{
			$data = [];
			if(!$url) {
				$url = 'edit/0/';
			}
		}

		if($params['data']){
			$data = $data + $params['data'];
		}

		if(!$index){
			$index = count($this->buttons);
		}

		$this->buttons[$index] = [
			'id' => ($params['id'] ?: 'btnNew' . $this->name),
			'type' => 'href',
			'icon' => ($params['icon'] ?: 'plus-circle'),
			'class' => ($params['class'] ?: 'outline-primary'),
			'name' => 'btnNew' . $this->name,
			'label' => $caption,
			'link' => $url,
			'data' => $data
		];
	}

	public function addOptionButton($caption, array $options, $params = [], $index = false){
	    $items = [];

	    foreach($options AS $option){
            $data = [];

            if($option['url']) {
                $url = $option['url'];
            }else{
                $url = false;
            }

            if($option['modal']) {
                $data = [
                    'target' => '#ajax-modal',
                    'toggle' => 'modal'
                ];

                if(!$url) {
                    if($option['form']){
                        $form = $option['form'];
                    }else{
                        $form = $this->formName;
                    }
                    $url = '/ajax/forms/' . $form . '/0' . ($this->parameters['foreignkeys'][0] ? '|' . $this->parameters['foreignkeys'][0] : '') . '/' . $this->name . '/';
                }

                if($option['size']){
                    $data['size'] = $option['size'];
                }

                if($option['backdrop'] === false){
                    $data['backdrop'] = 'static';
                    $data['keyboard'] = 'false';
                }
            }else{
                if(!$url) {
                    $url = 'edit/0/';
                }
            }

            $items[] = [
                'label' => $option['label'],
                'url' => $url,
                'class' => ($option['class'] ?: false),
                'icon' => ($option['icon'] ?: false),
                'data' => $data,
            ];
        }

        if(!$index){
            $index = count($this->buttons);
        }

        $this->buttons[$index] = [
            'id' => 'btnOptions' . $this->name,
            'type' => 'dropdown',
            'icon' => ($params['icon'] ?: false),
            'class' => ($params['class'] ?: 'btn-outline-primary'),
            'name' => 'btnOptions' . $this->name,
            'label' => $caption,
            'items' => $items,
        ];
    }

    public function addGroup($idField, $textField, $descriptionField = false){
        $this->rowGroups[] = [
            'id' => $idField,
            'name' => $textField,
            'description' => $descriptionField,
        ];
    }

    public function makeSortable($sortField, $groupField = false){
        $this->sortable = true;
        $this->sortField = $sortField;
        $this->sortGroupField = $groupField;
    }

    public function getUpdateFields(){
        return $this->fieldsToUpdate;
    }

    protected function setUpdateField(string $selector, enumJSActions $action, $value){
        $this->fieldsToUpdate[$selector][$action->getValue()] = $value;
        return $this;
    }
}
