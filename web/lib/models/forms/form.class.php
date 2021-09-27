<?php
class form extends model {
    const ModalTemplate = 'modalform';

    public $type = 'form';
	public $title = '';
	public $subTitle = '';
	public $boxed = true;
	public $sections = [];
	public $visible = true;
	public $controls;
	public $buttons;
	public $extraFields = [];
	public $upload = false;
	public $parameters;
	public $values;
	public $errors = [];
	public $cssClass = [
		'card'    => '',
		'header'  => '',
		'body'    => 'p-3',
		'footer'  => ''
	];

	public $dbTable;
	public $joins = [];
	public $keyFields;
	public $useSession = false;
	public $action;
	public $reloadPage = false;
	public $readonly = false;
	public $returnData = [];
	public $rights;
	public $customModalButtons = false;
	public $customRights = false;
	public $customActions = [];
	public $displayErrors = true;
	public $reCaptcha = []; // sitekey, secret, action
	public $locale;

	public $includeBefore = false;

	public $view = false;
	public $viewTemplate = false;
	public $toolsTemplate = false;

	private $reCaptchaTokenName = 'recaptcha-token';
	private $reCaptchaActionName = 'homepage';

	protected $inputs;
	protected $isValid = true;

	public $state;
	public $tabError = false;

	public function __construct($parameters = Array()) {
		parent::__construct();

		$this->action = './';
		$this->parameters = $parameters;
		if (!empty($this->parameters['action'])) {
			$this->action = $this->parameters['action'];
		}
		if (!empty($this->parameters['keyfields'])) {
			$this->keyFields = $this->parameters['keyfields'];
		}
	}

	public function init() {
		$this->state = FORM_STATE_INITED;
		$this->rights = (!empty($this->owner->originalPage)) ? $this->owner->originalPage : $this->owner->page;
		$this->locale = $this->owner->lib->getLocaleSettings();

		if (method_exists($this, 'setup')) {
			$this->setup();
		} else {
			$this->loadModel('forms', $this->name . '.form');
		}

		if($this->owner->user->isLoggedIn() && $this->rights) {
			if ($this->owner->user->getUser()['access_rights'][$this->rights] < ACCESS_RIGHT_WRITE) {
				$this->readonly = true;

				if($this->customRights == ACCESS_RIGHT_WRITE) {
					$this->readonly = false;
				}
			}
		}

		if($this->parameters['viewOnly'] && $this->viewTemplate){
			$this->view = $this->viewTemplate;
		}

        $this->onBeforeLoadValues();

		$this->inputs = [];

		if(!Empty($this->reCaptcha) && $this->reCaptcha['sitekey']){
			$this->controls['recaptcha'] = [
				'id' => ($this->reCaptcha['token'] ? $this->reCaptcha['token'] : $this->reCaptchaTokenName),
				'name' => ($this->reCaptcha['token'] ? $this->reCaptcha['token'] : $this->reCaptchaTokenName),
				'type' => 'recaptcha',
				'sql_skip' => true,
			];
		}

		$this->loadInputs($this->controls);

		if (empty($_REQUEST[$this->name])) {
			if ($this->useSession) {
				$this->values = $this->getSession('form_values_' . $this->name);
			}
			if (empty($this->values)) {
				$this->loadValues();
			}
			$this->state = FORM_STATE_LOADED;
		}else {
			$this->loadExtraValues();
		}
		if (empty($this->values)) {
			foreach($this->inputs as $cVal) {
				$this->values[ $cVal['name'] ] = $cVal['default'];
				if($cVal['select']){
                    $this->values[ $cVal['select']['name'] ] = $cVal['select']['default'];
                }
			}
		}

		$this->onAfterLoadValues();
		$this->controls = $this->parseInputs($this->controls);

        if (!empty($_REQUEST[$this->name])) {
			$this->state = FORM_STATE_REQUEST;
			$this->handleRequest($_REQUEST[$this->name]);
			$this->onAfterHandleRequest();

			if($this->buttons || $this->customActions){
				$validActions = [];
				if($this->buttons) {
					foreach ($this->buttons as $button) {
						if (!in_array($button['name'], $validActions) && !Empty($button['name'])) {
							$validActions[] = [
								'name' => $button['name'],
								'type' => 'button'
							];
						}
					}
				}

				if($this->customActions){
					foreach ($this->customActions as $action) {
						if (!in_array($action, $validActions)) {
							$validActions[] = [
								'name' => $action,
								'type' => 'custom'
							];
						}
					}
				}

				foreach($validActions as $action) {
					if (isset($_REQUEST[$this->name][$action['name']])) {
                        if ($this->validate() || $action['type'] == 'custom') {
							$this->state = FORM_STATE_VALIDATED;
							if (strtolower($action['name']) == 'save') {
								$this->state = FORM_STATE_SAVED;
								$this->saveValues();
							} else if (method_exists($this, $action['name'])) {
								$this->state = FORM_STATE_BUTTONACTION;
								call_user_func_array([$this, $action['name']], []);
							}
						} else {
							$this->state = FORM_STATE_INVALID;
						}
					}
				}
			}
		}

		if ($this->owner->page != 'ajax') {
			$this->owner->view->includeValidationJS();
		}

		$this->onAfterInit();
	}

	public function __destruct() {
		if (!empty($this->values) && $this->useSession) {
			$this->setSession('form_values_' . $this->name, $this->values);
		}
	}

	public function reset() {
		$this->values = null;
		if ($this->useSession) {
			$this->delSession('form_values_' . $this->name);
		}
		$this->state = FORM_STATE_RESETED;
	}

	public function handleRequest($request) {
		foreach($this->inputs as $key => $val) {
			if (in_array($val['type'], ['checkbox', 'checkboxonoff'])) $this->values[ $val['name'] ] = $val['valueoff'];
			if (!empty($val['name'])) {
				if (isset($request[$val['name']])) {
					$this->values[$val['name']] = $request[$val['name']];

					if($val['select']['name']){
						$this->values[$val['select']['name']] = $request[$val['select']['name']];
					}

				} else if (strpos($val['name'], '/') !== false) {
					$vPath = explode('/', $val['name']);
					$vPart = $request;
					$good = (count($vPath) > 0);
					foreach($vPath as $p) {
						if (isset($vPart[$p])) {
							$vPart = $vPart[$p];
						} else {
							$good = false;
							break;
						}
					}
					if ($good) {
						$this->values[$val['name']] = $vPart;
					}
				}
			}
		}
	}

	public function addError($code, $message, $type = 2, $controls = []) {
		$this->isValid = false;

		foreach($controls as $id) {
			$this->addInputError($id, $this->controls, true);
		}

		$this->errors[$code] = Array(
			'code'    => $code,
			'message' => $message,
			'type'    => $type, // (error level: info = 0, warning = 1, error = 2, error with details text = 3)
			'object'  => $this->name,
			'controls' => $controls,
		);
	}

	public function addInputError($id, &$controls, $errorCodes = []) {
		$this->isValid = false;
		$found = false;
		foreach($controls as $cKey => &$cVal) {
			if ($id == $cVal['id']) {
				$found = true;
				if (empty($cVal['error'])) {
					$cVal['error'] = [];
				}

				if(is_array($errorCodes)){
					$cVal['error'] = array_merge($cVal['error'], $errorCodes);
				}else{
					$cVal['error'] = true;
				}
			}
			if (!$found && !empty($cVal['group'])) {
				$found = $this->addInputError($id, $cVal['group'], $errorCodes);
			}
			if (!$found && !empty($cVal['controls'])) {
				$found = $this->addInputError($id, $cVal['controls'], $errorCodes);
				if($found && $cVal['type'] == 'tab'){
					$this->tabError = $cVal['id'];
				}
			}
			if ($found) break;
		}
		return $found;
	}

	private function loadInputs($controls){
		if (!empty($controls)) {
			foreach($controls as $key => $control) {

				if (!empty($control['name'])) {
					$this->inputs[] = $control;
				}
				if (!empty($control['group'])) {
					$controls[$key]['group'] = $this->parseInputs($control['group']);
				}
				if (!empty($control['controls'])) {
					$controls[$key]['controls'] = $this->parseInputs($control['controls']);
				}
			}
		}

		return $controls;
	}

	protected function parseInputs($controls) {
		if (!empty($controls)) {
			foreach($controls as $key => $control) {

				if (!empty($control['name'])) {

					if ($this->readonly) {
						$control['disabled'] = true;
						$controls[$key]['disabled'] = true;
						if (!empty($control['data']['connected-select'])) {
							unset($control['data']['connected-select']);
							unset($controls[$key]['data']['connected-select']);
						}
					}

					$this->inputs[] = $control;
					if ($control['type'] == 'htmleditor' && empty($control['readonly'])) {
						$this->owner->view->addCss('summernote/summernote-bs4.css', 'summernote');
						$this->owner->view->addJs('summernote/summernote-bs4.js', 'summernote');
						$this->owner->view->addJs('summernote/summernote-cleaner.js', 'summernote-cleaner');
						$this->owner->view->addJs('summernote/summernote-gallery-extension.js', 'summernote-gallery');

						$this->owner->view->addInlineJs("
							$('#" . $control['id'] . "').summernote({
								height: 300,
								toolbar: [
									['edit', ['undo', 'redo']],
									//['style', ['style']],
									['font', ['bold', 'italic', 'underline', 'fontsize', 'clear']],
									['color', ['color']],
									['para', ['ul', 'ol', 'paragraph']],
									['insert', ['table', 'link', 'gallery', 'video']], // 'picture'
									['misc', ['fullscreen', 'codeview']],
									['cleaner',['cleaner']]
								],
								cleaner:{
									action: 'both',
									newline: '<br>',
									notStyle: 'position:hidden;top:0;left:0;right:0',
									icon: '<i class=\"fa fa-eraser\"></i>',
									keepHtml: false,
									keepOnlyTags: ['<p>', '<br>', '<ul>', '<li>', '<b>', '<strong>','<i>', '<a>'],
									keepClasses: false,
									badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'],
									badAttributes: ['style', 'start', 'class'],
									limitChars: false,
									limitDisplay: 'none',
									limitStop: false
								},
								callbacks :{
									 onInit: function() {
										$(this).data('image_dialog_images_url', \"/ajax/gallery-content/\");
										$(this).data('image_dialog_title', \"\");
						                $(this).data('image_dialog_close_btn_text', \"" . $this->owner->translate->getTranslation('BTN_CLOSE') . "\");
                						$(this).data('image_dialog_ok_btn_text', \"" . $this->owner->translate->getTranslation('BTN_OK') . "\");
									}
								}
							});
						\n");
					} else if ($control['type'] == 'geolocation') {
						$this->owner->view->addJs("https://maps.googleapis.com/maps/api/js?key=" . $this->owner->settings['googleMapsAPI'] . "&callback=", 'googlemaps', true, true, false);
						$this->owner->view->addJs("geolocation.js", 'geolocation');
						$this->owner->view->addInlineJs(
							"initMap('" . $control['id'] . "');\n"
						);
					} else if ($control['type'] == 'fileinput'){
						$this->owner->view->addCss("fileinput/fileinput.min.css", 'fileinput', false, false);
						$this->owner->view->addJs("fileinput/fileinput.min.js", 'fileinput', false, false);
					} else if ($control['type'] == 'file'){
						$this->owner->view->addCss("fileuploader/jquery.fileuploader.min.css", 'fileupload');
						$this->owner->view->addCss("fileuploader/fileuploader-theme-thumbnails.css", 'fileupload-thumbnail');
						$this->owner->view->addCss("fileuploader/fileuploader-theme-gallery.css", 'fileupload-gallery');
						$this->owner->view->addJs("fileuploader/jquery.fileuploader.min.js", 'fileupload');

						$inline = $this->owner->view->renderContent(($control['config'] ?: 'fileupload.config'), $control['options'], false, false);
						$this->owner->view->addInlineJs($inline);
					} else if ($control['type'] == 'recaptcha' && $this->reCaptcha['sitekey']){
						$this->owner->view->addJs('https://www.google.com/recaptcha/api.js?render=' . $this->reCaptcha['sitekey'], 'recaptcha', true);
						$this->owner->view->addInlineJs("
							grecaptcha.ready(function() {
								grecaptcha.execute('" . $this->reCaptcha['sitekey'] . "', {action: '" . ($this->reCaptcha['action'] ?: $this->reCaptchaActionName) . "'}).then(function(token) {
									$('#" . ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName) . "').val(token);									
								});
							});"
						);
					}
				} else if ($control['type'] == 'table') {
					if ($this->readonly) {
						$control['table']->readonly = true;
					}
				}
				if (!empty($control['group'])) {
					$controls[$key]['group'] = $this->parseInputs($control['group']);
				}
				if (!empty($control['controls'])) {
					$controls[$key]['controls'] = $this->parseInputs($control['controls']);
				}
			}
		}

		return $controls;
	}

	public function loadExtraValues(){
		$select = [];

		if($this->extraFields){
            foreach($this->extraFields AS $alias => $query){
                $field = $query;
                if(is_array($query)){
                    $field = $query['field'];
                }

                if(!in_array($field, $select)) {
                    $select[] = $field;
                }
            }

			if($select) {
				$res = $this->owner->db->getFirstRow("SELECT " . implode(', ', $select) . " FROM " . $this->dbTable . $this->getJoins() . $this->owner->db->genSQLWhere($this->keyFields));
				if (!empty($res)) {
					foreach($this->extraFields AS $field => $query){
						if(!isset($this->values[$field])) {
							$this->values[$field] = $res[$field];
						}
					}
				}
			}
		}
	}

	public function loadValues() {
		if (!empty($this->dbTable) && !empty($this->keyFields)) {
			$select = [];

			foreach($this->inputs as $key => $val) {
				if(isset($val['subtype'])) continue;

				if (!empty($val['name']) && empty($val['sql_skip'])) {
					if (!empty($val['sql_select'])) {
						$select[] = $val['sql_select'] . ' as ' . $val['name'];
					} else if (strpos($val['name'], '/') === false && empty($val['db'])) {
						$select[] = $val['name'];
					}

					if($val['type'] == 'text' && $val['select']['name']){
						$select[] = $val['select']['name'];
					}
				}
			}

			if($this->extraFields){
				foreach($this->extraFields AS $field => $query){
                    $field = $query;
                    if(is_array($query)){
                        $field = $query['field'];
                    }

                    if(!in_array($field, $select)) {
                        $select[] = $field;
                    }
				}
			}

			$res = $this->owner->db->getFirstRow( "SELECT " . implode(', ', $select) . " FROM " . $this->owner->db->prepareTableName($this->dbTable) . $this->getJoins() . $this->owner->db->genSQLWhere($this->keyFields)	);
			if (!empty($res)) {
				foreach($this->inputs as $key => $val) {
					if(isset($val['subtype'])) continue;

					if (!empty($val['name']) && isset($res[$val['name']])) {
						switch ($val['type']) {
							case 'checkgroup':
								$res[$val['name']] = explode('|', trim($res[$val['name']], '|'));
								if (count($res[$val['name']]) == 1 && Empty($res[$val['name']][0])) $res[$val['name']] = [];
								break;
							case 'date':
								//$res[$val['name']] = $this->owner->lib->formatDate($res[$val['name']]);
								break;
							case 'text':
								if($val['select']['name'] && isset($res[$val['select']['name']])){
									$this->values[$val['select']['name']] = $res[$val['select']['name']];
								}
								break;
						}
						$this->values[$val['name']] = $res[$val['name']];
					} else if (!empty($val['db'])) {
						$this->values[$val['name']] = $this->loadSubTableValues($val);
					}
				}

				if($this->extraFields){
					foreach($this->extraFields AS $field => $query){
						if(!isset($this->values[$field])) {
							$this->values[$field] = $res[$field];
						}
					}
				}
			}
		}

		$this->onLoadValues();
	}

	private function loadSubTableValues($control) {
		$values = [];
		$keyValues = array_values($this->keyFields);
		if (!empty($control['db']['table']) && !empty($control['db']['foreignkey']) && !empty($keyValues[0])) {
			$res = $this->owner->db->getRows(
				"SELECT " . $control['name'] . " FROM " . $this->owner->db->prepareTableName($control['db']['table']) . " WHERE " . $control['db']['foreignkey'] . " = '" . $this->owner->db->escapeString($keyValues[0]) . "'"
			);
			if (!empty($res)) {
				foreach($res as $row) {
					$values[] = $row[$control['name']];
				}
			}
		}
		return $values;
	}

	private function getJoins(){
		$sql = '';

		if(!Empty($this->joins) && is_array($this->joins)){
			$sql = ' ' . implode(' ', $this->joins) . ' ';
		}

		return $sql;
	}

	public function validate() {
		$this->errors = [];
		$this->isValid = true;
		//$validating_types = ['email', 'number', 'url']; // not used

		if(!Empty($this->reCaptcha)){
			$this->isValid = $this->checkReCaptchaToken();
		}

		foreach($this->inputs as $input) {
			if (!empty($this->values[$input['name']])) {
				switch($input['type']) {
					case 'email':
						if (!checkEmail($this->values[$input['name']])) {
							//$this->addInputError($input['id'], $this->controls, [10002]);
							$this->addError(10002, '', 2, [$input['id']]);
						}
						break;
					case 'number':
						$this->values[$input['name']] *= 1;
						if (!is_int($this->values[$input['name']])) {
							//$this->addInputError($input['id'], $this->controls, [10003]);

							$this->addError(10003, '', 2, [$input['id']]);
						}
						break;
					case 'url':
						if (!filter_var($this->values[$input['name']], FILTER_VALIDATE_URL)) {
							//$this->addInputError($input['id'], $this->controls, [10004]);

							$this->addError(10004, '', 2, [$input['id']]);
						}
						break;
				}
			}

			if (!empty($input['constraints'])) {
				foreach($input['constraints'] as $constraint => $cValue) {
					switch($constraint) {
						case 'required':
							if (!empty($cValue) && empty($this->values[$input['name']])
								&& ($input['type'] != 'number' || $this->values[$input['name']] == '')) {
								//$this->addInputError($input['id'], $this->controls, [1000]);

								$this->addError(1000, '', 2, [$input['id']]);
							}
							break;
						case 'equalto':
							$cValue = str_replace('#', '', $cValue);
							if (!empty($cValue) && !empty($this->values[$input['name']])) {
								$checkValue = '';
								foreach($this->inputs AS $imp){
									if($imp['id']==$cValue){
										$name = $imp['name'];
										$checkValue = $this->values[$name];
										break;
									}
								}

								if($checkValue != $this->values[$input['name']]){
									//$this->addInputError($input['id'], $this->controls, [1001]);

									$this->addError(1001, '', 2, [$input['id']]);
								}
							}
							break;
					}
				}
			}
		}

		$this->onValidate();
		return $this->isValid;
	}

	public function saveValues() {
		$this->onBeforeSave();

		if(!Empty($this->reCaptcha)){
			$tokenName = ($this->reCaptcha['token'] ? $this->reCaptcha['token'] : $this->reCaptchaTokenName);
			unset($this->values[$tokenName]);
		}

		$statement = '';
		if (!empty($this->dbTable) && !empty($this->keyFields)) {
			$keyValues = array_values($this->keyFields);
			$values = $this->values;
			$subTableControls = [];

            if($this->extraFields) {
                foreach ($this->extraFields as $alias => $query) {
                    if(is_array($query) && $query['exclude']){
                        unset($values[$alias]);
                    }
                }
            }

			foreach($this->inputs as $key => $val) {
				if (isset($val['subtype'])) {
					unset($values[$val['name']]);
				} elseif (!empty($val['sql_skip']) && !empty($val['name'])) {
					unset($values[$val['name']]);
				} elseif ($val['readonly'] && empty($values[$val['name']])) {
					unset($values[$val['name']]);
				} else {
					if (!empty($val['db'])) {
						$subTableControls[] = [
							'control' => $val,
							'values' => $values[$val['name']]
						];
						unset($values[$val['name']]);
					}
					if (strpos($val['name'], '/') !== false) {
						unset($values[$val['name']]);
					}
					switch($val['type']) {
						case 'checkgroup':
							if (isset($values[$val['name']])
								&& is_array($values[$val['name']])) {
								$values[$val['name']] = '|' . implode('|', $values[$val['name']]) . '|';
							}
							break;
						case 'date':
							if (empty($values[$val['name']])) {
								$values[$val['name']] = null;
							}
							break;
					}
				}
			}

			if (empty($keyValues[0])) {
				$statement = 'insert';
				$insertValues = $values;
				$kidx = 0;
				foreach($this->keyFields as $key => $val) {
					if (!empty($val) || $kidx > 0) {
						$insertValues[$key] = $val;
					}
					$kidx++;
				}

				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLInsert($this->dbTable, $insertValues)
				);
				$keyFields = array_keys($this->keyFields);
				$this->keyFields[$keyFields[0]] = $this->owner->db->getInsertRecordId();
			} else {
				$statement = 'update';
				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLUpdate($this->dbTable, $values, $this->keyFields)
				);
			}
			if (!empty($subTableControls)) {
				$keyValues = array_values($this->keyFields);
				foreach($subTableControls as $val) {
					foreach($val['control']['options'] as $opt_key => $opt_val) {
						$record = [
							$val['control']['db']['foreignkey'] => $keyValues[0],
							$val['control']['name'] => $opt_key
						];
						if (in_array($opt_key, $val['values'])) {
							$this->owner->db->sqlQuery(
								$this->owner->db->genSQLInsert($val['control']['db']['table'], $record, [$val['control']['db']['foreignkey']])
							);
						} else {
							$this->owner->db->sqlQuery(
								$this->owner->db->genSQLDelete($val['control']['db']['table'], $record)
							);
						}
					}
				}
			}
		}

		$this->onAfterSave($statement);
	}

	protected function loadSubTable($tableName, $alias = false, $params = []) {
		$params['foreignkeys'] = array_values($this->keyFields);
		return $this->owner->loadTable($tableName, $params, $alias);
	}

	private function checkReCaptchaToken(){
		$out = true;
		if(!Empty($this->reCaptcha) && $this->reCaptcha['secret']){
			$actionName = ($this->reCaptcha['action'] ? $this->reCaptcha['action'] : $this->reCaptchaActionName);
			$tokenName = ($this->reCaptcha['token'] ? $this->reCaptcha['token'] : $this->reCaptchaTokenName);
			$response = file_get_contents(
				"https://www.google.com/recaptcha/api/siteverify?secret=" . $this->reCaptcha['secret'] . "&response=" . $this->values[$tokenName] . "&remoteip=" . $_SERVER['REMOTE_ADDR']
			);
			$response = json_decode($response, true);
			if($response['success'] && $response['action'] == $actionName){
				$out = true;
			}else{
				$this->addError('RECAPTCHA', 'reCaptcha error', 2, []);
			}
		}

		return $out;
	}

	public function getControlById($id, $controls = []) {
		$result = false;
		if (empty($controls)) $controls = $this->controls;
		foreach($controls as $cKey => $cVal) {
			if ($id == $cVal['id']) {
				$result = $cVal;
			}
			if (!$result && !empty($cVal['group'])) {
				$result = $this->getControlById($id, $cVal['group']);
			}
			if (!$result && !empty($cVal['controls'])) {
				$result = $this->getControlById($id, $cVal['controls']);
			}
			if ($result) break;
		}
		return $result;
	}

	public function arrayToValues($array, $prefix = '') {
		$result = [];
		if (is_array($array)) {
			foreach ($array as $key => $val) {
				if (is_array($val)) {
					$result = array_merge($result, $this->arrayToValues($val, $prefix . $key . '/'));
				} else {
					$result[$prefix . $key] = $val;
				}
			}
		}
		return $result;
	}

	protected function removeControl($id){
		$this->controls = $this->updateControls($this->controls, $id, [], 'DELETE');
	}

	protected function setControlProperties($id, array $properties){
		$this->controls = $this->updateControls($this->controls, $id, $properties);
	}

	private function updateControls($controls, $id, array $properties, $method = 'CHANGE'){
		if(!Empty($controls)) {
			foreach ($controls AS $key => $control) {
				if($control['id'] == $id){
					switch ($method){
						case 'DELETE':
							unset($controls[$key]);
							break 2;

						case 'CHANGE':
						default:
							if(!Empty($properties)){
								foreach($properties AS $propName => $value){
									if($propName == 'data'){
										foreach($value AS $dataKey => $dataValue){
											$controls[$key][$propName][$dataKey] = $dataValue;
										}
									}else {
										$controls[$key][$propName] = $value;
									}
								}
								break 2;
							}
							break;
					}
				}

				if (!empty($control['group'])) {
					$controls[$key]['group'] = $this->updateControls($control['group'], $id, $properties, $method);
				}
				if (!empty($control['controls'])) {
					$controls[$key]['controls'] = $this->updateControls($control['controls'], $id, $properties, $method);
				}
			}
		}

		return $controls;
	}

	public function onBeforeLoadValues() {
	}

	public function onAfterLoadValues() {
	}

	public function onAfterHandleRequest() {
	}

	public function onValidate() {
	}

	public function onBeforeSave() {
	}

	public function onAfterSave($statement) {
	}

	public function onLoadValues(){
	}

	public function onAfterInit(){
	}
}
