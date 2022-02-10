<?php
class administratorsTable extends table {

	public function setup() {
		$this->dbTable = 'users';
		$this->keyFields = ['us_id'];
		$this->foreignKeyFields = ['us_group'];
		$this->where = 'us_group = "' . USER_GROUP_ADMINISTRATORS . '" AND us_shop_id = ' . $this->owner->shopId;

		$this->subTable = true;
		$this->delete = true;
		$this->header = true;
        $this->modalSize = 'lg';
		$this->deleteField = 'us_deleted';
		$this->additionalOptionsTemplate = 'table_options_user';

		$this->settings['display']    = 10;
		$this->settings['orderfield'] = 'us_lastname ASC, us_firstname';
		$this->settings['orderdir']   = 'asc';

        $this->formName = 'administrator';
        $this->parameters['foreignkeys'][0] = $this->owner->user->group;

        $this->addColumns(
            new column('us_enabled', 'LBL_ENABLED_SHORT', 1, enumTableColTypes::Checkbox()),
            (new column('us_firstname', 'LBL_NAME', 5))->setTemplate('{{ formatName(val, row.us_lastname) }}'),
            (new column('us_role', 'LBL_ROLE', 2))->setTemplate('{{ userRole(val)|raw }}'),
            (new column('us_last_login', 'LBL_LAST_LOGIN', 2))->setTemplate('{{ _date(val, 5) }}'),
            new columnHidden('us_lastname')
        );

		$this->addButton(
			'BTN_NEW_USER',
			true,
            [
                'size' => 'lg'
            ]
		);
	}

	public function onAfterDelete($keyFields, $real = true) {
		$sql = "SELECT us_email FROM " . DB_NAME_WEB . ".users WHERE us_id = '" . $keyFields['us_id'] . "' AND us_shop_id = " . $this->owner->shopId;
		$email = $this->owner->db->getFirstRow($sql);

		if($email) {
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					DB_NAME_WEB . '.users',
					[
						'us_enabled' => 0,
						'us_email' => 'xxx|' . $email['us_email'] . '|xxx'
					],
					[
						'us_id' => $keyFields['us_id']
					]
				)
			);
		}
	}

	public function sendPassword($keyFields){
		$sql = "SELECT us_password_sent, us_email FROM " . DB_NAME_WEB . ".users WHERE us_id = '" . (int) $keyFields['us_id'] . "' LIMIT 1";
		$user = $this->owner->db->getFirstRow($sql);
		if($user){
            $counter = (int) $user['us_password_sent'];
			if($counter > 0){
				$template = 'request-new-password';
			}else{
				$template = 'new-password';
			}

			$data = [
				'id' => $keyFields['us_id'],
				'email' => $user['us_email'],
				'heroImg' => 'user-reset-password.png',
				'link' => $this->owner->user->setId($keyFields['us_id'])->getPasswordChangeLink(false, false, 24*7),
			];

			$sent = $this->owner->email->prepareEmail($template, $keyFields['us_id'], $data);
			if($sent == 1){
				$this->owner->addMessage(router::MESSAGE_SUCCESS, '', 'LBL_PASSWORD_SENT_SUCCESSFULLY');
			}else{
				$this->owner->addMessage(router::MESSAGE_DANGER, '', 'LBL_PASSWORD_WAS_NOT_SENT');
			}

			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					DB_NAME_WEB . '.users',
					[
						'us_password_sent' => 'INCREMENT',
					],
					[
						'us_id' => $keyFields['us_id']
					]
				)
			);

			$this->owner->pageRedirect('/settings/system/administrators/');
		}
	}

	public function onAfterLoad() {
		if($this->rows){
			foreach($this->rows AS $key => $row){
				$this->rows[$key]['options']['delete'] = $this->hasHigherRole($row['us_id'], $row['us_role']);
			}
		}
	}

	public function isDeleteable($keyFields) {
		$deleteAble = true;

        $sql = "SELECT us_id, us_role FROM " . DB_NAME_WEB . ".users WHERE us_shop_id = " . $this->owner->shopId . " AND  us_id = '" . (int) $keyFields['us_id'] . "' LIMIT 1";
        $user = $this->owner->db->getFirstRow($sql);
        if($user){
            $deleteAble = $this->hasHigherRole($user['us_id'], $user['us_role']);
        }

		return $deleteAble;
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

	private function hasHigherRole($userId, $userRole){
		if($userId != $this->owner->user->id){
			$editorRoleLevel = $this->getRoleLevel($this->owner->user->getRole());
			$userRoleLevel = $this->getRoleLevel($userRole);
			return ($editorRoleLevel <= $userRoleLevel);
		}else {
			return false;
		}
	}
}
