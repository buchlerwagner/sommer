<?php
class administratorForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(
            [
                'us_id' => 0
            ]
        );
    }

    public function setup() {
		$this->dbTable = 'users';
		$this->rights = 'administrators';

		$this->title = 'LBL_EDIT_ADMINISTRATOR';

        $general = (new groupFieldset('general-data'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputSelect('us_title', 'LBL_PERSON_TITLE'))->setColSize('col-2')->setOptions($this->owner->lib->getList('titles')),
                (new inputText('us_lastname', 'LBL_LASTNAME'))->setRequired()->setColSize('col-5'),
                (new inputText('us_firstname', 'LBL_FIRSTNAME'))->setRequired()->setColSize('col-5')
            ),
            (new inputText('us_email', 'LBL_EMAIL'))->setRequired(),
            (new inputText('us_phone', 'LBL_PHONE'))
        );

        $role = (new groupFieldset('user-role'))->addElements(
            (new inputSelect('us_role', 'LBL_ROLE', 'USER'))
        );

        $this->addTabs(
            (new sectionTab('general', 'LBL_GENERAL', '', true))->addElements($general),
            (new sectionTab('roles', 'LBL_USER_ROLES'))->addElements($role)
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

	public function onAfterLoadValues() {
		$editorRole = $this->owner->user->getRole();
		$editorRoleLevel = $this->getRoleLevel($editorRole);

		if(Empty($this->keyFields['us_id'])){
			$options = $this->owner->lib->getList('roles', ['group' => USER_GROUP_ADMINISTRATORS, 'limit' => $editorRole]);
		}else{
			$userRoleLevel = $this->getRoleLevel($this->values['us_role']);
			if($userRoleLevel < $editorRoleLevel){
                $options = $this->owner->lib->getList('roles', ['group' => USER_GROUP_ADMINISTRATORS, 'limit' => false]);
                $this->changeControlProperty('us_role', 'setReadonly', true);
			}else{
                $options = $this->owner->lib->getList('roles', ['group' => USER_GROUP_ADMINISTRATORS, 'limit' => $editorRole]);
			}
        }

        $this->changeControlProperty('us_role', 'setOptions', $options);
    }

	public function onLoadValues() {
		if(!$this->values && !Empty($this->keyFields['us_id'])){
			$this->owner->pageRedirect('/settings/system/administrators/');
		}
	}

	public function onValidate() {
		if (!empty($this->values['us_email'])) {
			$res = $this->owner->db->getFirstRow(
				"SELECT us_id FROM " . DB_NAME_WEB . ".users WHERE us_shop_id = " . $this->owner->shopId . " AND us_email LIKE \"" . $this->owner->db->escapeString($this->values['us_email']) . "\" AND us_id != '" . $this->keyFields['us_id'] . "'"
			);
			if (!empty($res)) {
				$this->addError('ERR_10009', self::FORM_ERROR, ['us_email']);
			}
		}
	}

	public function onBeforeSave() {
		if(!$this->keyFields['us_id']){
			$this->values['us_password'] = password_hash($this->values['us_email'] . microtime(true), PASSWORD_DEFAULT);
			$this->values['us_hash'] = uuid::v4();
		}

		$this->keyFields['us_group'] = USER_GROUP_ADMINISTRATORS;
		$this->values['us_shop_id'] = $this->owner->shopId;
	}

	public function onAfterSave($statement) {
        $this->owner->user->clearUserDataCache($this->keyFields['us_id']);
    }

    public function onAfterInit() {
        $this->owner->view->addInlineJs("
			$('#us_role').trigger('change');
        ");
    }

    private function getRoleLevel($role){
		$roleLevel = 0;
		$level = 0;
		foreach($GLOBALS['USER_ROLES'][USER_GROUP_ADMINISTRATORS] AS $key => $value){
			if($role == $key){
				$roleLevel = $level;
				break;
			}
			$level++;
		}

		return $roleLevel;
	}

}
