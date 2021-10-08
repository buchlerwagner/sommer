<?php
abstract class formBuilder extends model {
    const ModalTemplate = 'formBuilderModal';

    const FORM_ERROR    = 3;
    const FORM_WARNING  = 2;
    const FORM_INFO     = 1;
    const FORM_SUCCESS  = 0;

    public $type = 'formBuilder';
    public $formWidth = 'col-12';
	public $title = '';
	public $subTitle = '';
	public $boxed = true;
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
	public $keyFields = [];
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
	public $locale;

	public $includeBefore = false;

	public $view = false;
	public $viewTemplate = false;
	public $toolsTemplate = false;

    private $reCaptcha = [];
	private $reCaptchaTokenName = 'token';
	private $reCaptchaActionName = 'homepage';

    public $sections = [];
    public $controls = [];
    protected $inputs = [];
	protected $isValid = true;
	protected $inView = false;

	public $state;

	abstract public function setupKeyFields();
	abstract public function setup();

	public function __construct($parameters = []) {
		parent::__construct();

        $this->action = './';
		$this->parameters = $parameters;
		if (!empty($this->parameters['action'])) {
			$this->action = $this->parameters['action'];
		}
		if (!empty($this->parameters['viewOnly'])) {
			$this->inView = true;
		}
        $this->loadKeyFields();

        $this->rights = (!empty($this->owner->originalPage)) ? $this->owner->originalPage : $this->owner->page;

        if (method_exists($this, 'setRights')) {
            $this->setRights();
        }
	}

	public function init() {
        $this->onBeforeSetup();

		$this->state = FORM_STATE_INITED;
		$this->locale = $this->owner->lib->getLocaleSettings();

		if($this->owner->user->isLoggedIn() && $this->rights) {
			if (!$this->owner->user->getUser()['access_rights'][$this->rights] || $this->owner->user->getUser()['access_rights'][$this->rights] < ACCESS_RIGHT_WRITE) {
				$this->readonly = true;

                if($this->customRights == ACCESS_RIGHT_WRITE) {
                    $this->readonly = false;
                }
            }
		}

        if (method_exists($this, 'setup')) {
            $this->setup();
        } else {
            $this->loadModel('forms', $this->name . '.form');
        }

		if($this->parameters['viewOnly'] && $this->viewTemplate){
			$this->view = $this->viewTemplate;
		}

        $this->onBeforeLoadValues();

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
			    if (strpos($cVal['name'], '][') === false) {
                    $this->values[$cVal['name']] = $cVal['default'];
			    }
			}
		}

		$this->onAfterLoadValues();

		if (!empty($_REQUEST[$this->name])) {
			$this->state = FORM_STATE_REQUEST;
			$this->handleRequest($_REQUEST[$this->name]);
			$this->onAfterHandleRequest();

            if($this->buttons || $this->customActions){
				$validActions = [];
				if($this->buttons) {
					foreach ($this->buttons as $button) {
						if (!in_array($button->getName(), $validActions) && !Empty($button->getName())) {
							$validActions[] = [
								'name' => $button->getName(),
								'type' => 'button',
								'skipValidation' => false,
							];
						}
					}
				}

				if($this->customActions){
					foreach ($this->customActions as $action) {
						if (!in_array($action, $validActions)) {
							$validActions[] = [
								'name' => $action,
								'type' => 'custom',
                                'skipValidation' => false,
							];
						}
					}
				}

				foreach($validActions as $action) {
					if (isset($_REQUEST[$this->name][$action['name']])) {
                        if ($this->validate($action['skipValidation'])) {

							$this->state = FORM_STATE_VALIDATED;
							if (strtolower($action['name']) == 'save' && !$this->readonly) {
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

    public function setKeyFields(array $fields){
        $this->keyFields = $fields;
    }

    private function loadKeyFields(){
        if (empty($this->parameters['keyfields'])) {
            $this->setupKeyFields();
            if(!isAssociativeArray($this->keyFields) && !Empty($this->keyFields)) {
                $fields = $this->keyFields;
                $this->keyFields = [];

                foreach ($fields as $key) {
                    $this->keyFields[$key] = 0;
                }
            }
        }else{
            $this->keyFields = $this->parameters['keyfields'];
        }

        if (!empty($this->parameters['keyvalues']) && $this->keyFields) {
            foreach(array_keys($this->keyFields) as $key => $val) {
                if (isset($this->parameters['keyvalues'][$key])) {
                    $this->keyFields[$val] = $this->parameters['keyvalues'][$key];
                }
            }
        }
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

	private function handleRequest($request) {
        foreach($this->inputs as $key => $val) {
			if (in_array($val['type'], ['checkbox', 'checkboxonoff']) && Empty($val['name'])) $this->values[ $val['id'] ] = $val['valueoff'];

			if (!empty($val['name'])) {
				if (isset($request[$val['name']])) {
					$this->values[$val['name']] = $request[$val['name']];
				} else if (strpos($val['name'], '][') !== false) {
					$vPath = explode('][', $val['name']);
                    $mainKey = $vPath[0];
                    unset($vPath[0]);
                    if(!Empty($vPath)){
                        foreach($vPath as $p) {
                            if(isset($request[$mainKey][$p])){
                                $this->values[$mainKey][$p] = $request[$mainKey][$p];
                                break;
                            }
                        }
                    }
				}
			}
		}
    }

	protected function addError($message, $type = self::FORM_ERROR, $controls = []) {
		$this->isValid = false;

		foreach($controls as $id) {
            $element = $this->changeControlProperty($id, 'setError');
            if($element){
                if($sectionId = $element->getSectionId()){
                    $this->resetSections();
                    $this->sections['items'][$sectionId]['active'] = true;
                }
            }
		}

		$this->errors[$message] = [
			'message'  => $message,
			'type'     => $type,
			'controls' => $controls,
        ];
	}

	private function loadExtraValues(){
		$select = [];

		if($this->extraFields){
            foreach($this->extraFields AS $query){
                $field = $query['field'];

                if($query['query']){
                    $field = $query['query'];
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

	protected function loadValues() {
		if (!empty($this->dbTable) && !empty($this->keyFields)) {
			$select = [];

			foreach($this->inputs as $key => $val) {
				if (!empty($val['name']) && $val['DbField']) {
					if (!empty($val['sql_select'])) {
						$select[] = $val['sql_select'] . ' as ' . $val['name'];
					} else if (strpos($val['name'], '/') === false && empty($val['db'])) {
						$select[] = $val['name'];
					}
				}
			}

			if($this->extraFields){
				foreach($this->extraFields AS $query){
                    $field = $query['field'];

                    if($query['query']){
                        $field = $query['query'];
                    }

                    if(!in_array($field, $select)) {
                        $select[] = $field;
                    }
				}
			}

			$res = $this->owner->db->getFirstRow( "SELECT " . implode(', ', $select) . " FROM " . $this->owner->db->prepareTableName($this->dbTable) . $this->getJoins() . $this->owner->db->genSQLWhere($this->keyFields));
			if (!empty($res)) {
				foreach($this->inputs as $key => $val) {
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

	private function getJoins(){
		$sql = '';

		if(!Empty($this->joins) && is_array($this->joins)){
			$sql = ' ' . implode(' ', $this->joins) . ' ';
		}

		return $sql;
	}

	private function validate($skipValidation = false) {
		$this->errors = [];
		$this->isValid = true;

        if($skipValidation){
            return $this->isValid;
        }

        if(!Empty($this->reCaptcha)){
			$this->isValid = $this->checkReCaptchaToken();
		}

		foreach($this->inputs as $input) {
			if (!empty($this->values[$input['name']])) {
				switch($input['type']) {
					case 'email':
						if (!checkEmail($this->values[$input['name']])) {
							$this->addError('ERR_10002', self::FORM_ERROR, [$input['id']]);
						}
						break;
					case 'number':
						$this->values[$input['name']] *= 1;
						if (!is_int($this->values[$input['name']])) {
							$this->addError('ERR_10003', self::FORM_ERROR, [$input['id']]);
						}
						break;
					case 'url':
						if (!filter_var($this->values[$input['name']], FILTER_VALIDATE_URL)) {
							$this->addError('ERR_10004', self::FORM_ERROR, [$input['id']]);
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

								$this->addError('ERR_1000', self::FORM_ERROR, [$input['id']]);
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
									$this->addError('ERR_1000', self::FORM_ERROR, [$input['id']]);
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

	protected function saveValues() {
		$this->onBeforeSave();

		if(!Empty($this->reCaptcha)){
			$tokenName = ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName);
			unset($this->values[$tokenName]);
		}

		$statement = '';
		if (!empty($this->dbTable) && !empty($this->keyFields)) {
			$keyValues = array_values($this->keyFields);
			$values = $this->values;

            if($this->extraFields) {
                foreach ($this->extraFields as $alias => $query) {
                    if(is_array($query) && $query['exclude']){
                        unset($values[$alias]);
                    }
                }
            }

			foreach($this->inputs as $key => $val) {
                if (!$val['DbField'] || ($val['readonly'] && !empty($values[$val['name']]))) {
                    if (strpos($val['name'], '][') !== false) {
                        $k = strstr($val['name'], '][', true);
                        unset($values[$k]);
                    }else {
                        unset($values[$val['name']]);
                    }
                } else {
                    if (strpos($val['name'], '][') !== false) {
                        unset($values[strstr($val['name'], '][', true)]);
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
		}

		$this->onAfterSave($statement);
	}

	protected function loadSubTable($tableName, $alias = false, $params = []) {
		$params['foreignkeys'] = array_values($this->keyFields);
		return $this->owner->loadTable($tableName, $params, $alias);
	}

	private function checkReCaptchaToken(){
		$out = false;

		if(!Empty($this->reCaptcha) && $this->reCaptcha['secret']){
			$actionName = ($this->reCaptcha['action'] ?: $this->reCaptchaActionName);
			$tokenName = ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName);
			$response = file_get_contents(
				"https://www.google.com/recaptcha/api/siteverify?secret=" . $this->reCaptcha['secret'] . "&response=" . $this->values[$tokenName] . "&remoteip=" . $_SERVER['REMOTE_ADDR']
			);
            $this->reCaptcha['response'] = json_decode($response, true);

			if($this->reCaptcha['response']['success'] && $this->reCaptcha['response']['action'] == $actionName){
				$out = true;
			}else{
				$this->addError('ERR_RECAPTCHA', 2, []);
			}
		}

		return $out;
	}

    protected function useCaptcha($action = false){
        $this->reCaptcha = [];

        if($this->owner->settings['captcha'] && $this->owner->settings['googleSiteKey'] && $this->owner->settings['googleSecret']) {
            $this->setRecaptchaCredentials(
                $this->owner->settings['googleSiteKey'],
                $this->owner->settings['googleSecret'],
                false,
                $action
            );
        }

        return $this;
    }

    protected function setRecaptchaCredentials($siteKey, $secret, $token = false, $action = false){
        $this->reCaptcha = [
            'sitekey' => $siteKey,
            'secret' => $secret,
            'token' => ($token ?: $this->reCaptchaTokenName),
            'action' => ($action ?: $this->reCaptchaActionName)
        ];

        $this->addControl(
            (new inputRecaptcha(
                ($this->reCaptcha['token'] ?: $this->reCaptchaTokenName),
                $this->reCaptcha['sitekey'],
                ($this->reCaptcha['action'] ?: $this->reCaptchaActionName)
            ))
        );

        return $this;
    }

    protected function getRecaptchaResponse(){
        return $this->reCaptcha['response'];
    }

    public function hasCaptcha(){
        return (!Empty($this->reCaptcha['sitekey']));
    }

    protected function addButtons(formButton ...$buttons){
        foreach ($buttons as $button) {
            if ($button->getName() === 'cancel' && $this->parameters['backurl']) {
                $button->setUrl($this->parameters['backurl']);
            }
            if ($button->getName() === 'edit' && $this->parameters['editUrl']) {
                $button->setUrl($this->parameters['editUrl']);
            }

            $button->setReadOnly($this->readonly)
                    ->setForm($this->name);
            $this->buttons[$button->getId()] = $button->init();
        }
    }

    protected function addSections(sectionBox  ...$boxes){
        foreach ($boxes as $section) {
            $this->addSection($section);

            foreach($section->getElements() AS $element) {
                $this->addControl($element, $section->getId());
            }
        }
        return $this;
    }

    protected function addTabs(sectionTab ...$tabs){
        foreach ($tabs as $section) {
            $this->addSection($section);

            foreach($section->getElements() AS $element) {
                $element->setSectionId($section->getId());
                $this->addControl($element, $section->getId());
            }
        }
        return $this;
    }

    protected function removeSection($id){
        unset($this->sections['items'][$id]);
    }

    protected function hideSidebar($hide = true){
	    $this->sections['hideSidebar'] = $hide;
	    return $this;
    }

	protected function addControls(formControl ...$controls){
        foreach ($controls as $control) {
            $this->addControl($control);
        }
    }

    protected function getControl($id):formControl{
        if(!$element = $this->findElement($this->controls, $id)) {
            throw new Exception("The selected element ID ($id) not found!");
        }

        return $element;
    }

    protected function changeControlProperty($id, $method, ...$values):formControl{
	    if($element = $this->findElement($this->controls, $id)) {
            if (method_exists($element, $method)) {
                if($values) {
                    try {
                        $reflectionMethod = new ReflectionMethod($element, $method);
                        $reflectionMethod->invokeArgs($element, $values);
                    } catch (Exception $e) {
                        die('Invalid method call. Error: ' . $e->getMessage());
                    }
                }else{
                    $element->$method();
                }
            }else{
                throw new Exception("The given method ($method) is not exist!");
            }
        }else{
            throw new Exception("The selected element ID ($id) not found!");
        }

	    return $element;
    }

    protected function removeControl($id){
        $this->deleteElement($this->controls, $id);
        return $this;
    }

    protected function insertElementToGroup($id, formControl ...$controls){
        if($element = $this->findElement($this->controls, $id)) {
            if($element instanceof formControl) {
                if ($element->isContainer()) {
                    foreach ($controls as $control) {
                        $this->addInputs($control);
                        $element->addElements($control);
                    }
                } else {
                    throw new Exception("The selected element ID ($id) must be a container (formContainer)!");
                }
            }else{
                throw new Exception("The selected element ID ($id) must be a formControl type!");
            }
        }else{
            throw new Exception("The selected element ID ($id) not found!");
        }
    }

    protected function setSubtitle($subtitle){
	    if(!Empty($subtitle)) {
            $this->subTitle = $subtitle;
        }
    }

    protected function addExtraField($field, $excludeFromUpdate = true, $selectQuery = false){
        $this->extraFields[$field] = [
            'field' => $field,
            'query' => ($selectQuery ? $selectQuery . ' AS ' . $field : false),
            'exclude' => $excludeFromUpdate
        ];
    }

    private function findElement(&$object, $id){
	    $result = false;
        foreach($object AS $elementId => &$element){
            if($elementId == $id){
                return $element;
            }else {
                if ($element instanceof formControl) {
                    if ($element->isContainer() && !$result) {
                        $result = $this->findElement($element->getElementsByRef(), $id);
                    }
                } else {
                    if(!$result) {
                        $result = $this->findElement($element, $id);
                    }
                }
            }
        }

        return $result;
    }

    private function deleteElement(&$object, $id){
        unset($object[$id]);
        unset($this->inputs[$id]);

        foreach($object AS $elementId => &$element){
            if ($element instanceof formControl) {
                if ($element->isContainer()) {
                    $this->deleteElement($element->getElementsByRef(), $id);
                }
            } else {
                $this->deleteElement($element, $id);
            }
        }

        return $this;
    }

    private function resetSections(){
	    foreach($this->sections['items'] AS $id => $section){
            $this->sections['items'][$id]['active'] = false;
        }
    }

    private function addSection(formSections $section){
        $this->sections['type'] = $section->getType();
        $this->sections['items'][$section->getId()] = [
            'id' => $section->getId(),
            'title' => $section->getTitle(),
            'icon' => $section->getIcon(),
            'active' => $section->isActive(),
            'text' => $section->getText(),
            'class' => $section->getClass(),
        ];
    }

    private function addControl(formControl $control, $section = false){
        $this->addInputs($control);

        if($section) {
            $this->controls[$section][$control->getId()] = $control;
        }else{
            $this->controls[$control->getId()] = $control;
        }
    }

    private function addInputs(formControl $control){
	    if($control->isContainer()){
            $this->addArrayOfInputs($control->getElements());
        }else{
            if($this->readonly){
                $control->setDisabled();
            }

            $inputName = $control->getName();
            if(strpos($inputName, '][') !== false){
                $isDbField = false;
            }else{
                $isDbField = $control->isDBField();
            }
            $this->inputs[$control->getId()] = [
                'id' => $control->getId(),
                'name' => $control->getName(),
                'type' => $control->getType(),
                'default' => $control->getDefault(),
                'constraints' => $control->getConstraints(),
                'DbField' => $isDbField,
            ];

            if($css = $control->getCss()){
                foreach($css AS $name => $file){
                    $this->owner->view->addCss($file, $name);
                }
            }

            if($js = $control->getJs()){
                foreach($js AS $name => $file){
                    $this->owner->view->addJs($file, $name);
                }
            }

            if($inlineJs = $control->setInlineJs()){
                $this->owner->view->addInlineJs($inlineJs);
            }
        }
    }

    private function addArrayOfInputs(array $controls){
        if(!Empty($controls)) {
            foreach ($controls as $control) {
                $this->addInputs($control);
            }
        }
    }

    public final function hasError($controlId){
        return $this->getControl($controlId)->hasError();
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

    public function onBeforeSetup(){
    }
}
