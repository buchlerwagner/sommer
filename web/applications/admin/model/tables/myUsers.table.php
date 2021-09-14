<?php
class myUsersTable extends table {

	public function setup() {
		$this->dbTable = 'users';
		$this->join = 'LEFT JOIN user_groups ON (ug_id = us_ug_id)';
		$this->keyFields = ['us_id', 'us_ug_id'];
        $this->foreignKeyFields = ['us_ug_id'];
        $this->where = 'us_group = "' . USER_GROUP_PARTNERS . '" AND us_ug_id = ' . $this->owner->user->getGroupId();

        $this->subTable = true;
		$this->delete = false;
		$this->header = true;
		$this->view = true;
		$this->edit = false;
		$this->additionalOptionsTemplate = 'table_options_myusers';

		$this->settings['display']    = 10;
		$this->settings['orderfield'] = 'us_lastname ASC, us_firstname';
		$this->settings['orderdir']   = 'asc';

		$this->modalSize = 'lg';
        $this->formName = 'editMyUser';

        $this->addColumns(
            new column('us_enabled', 'LBL_ENABLED_SHORT', 1, enumTableColTypes::Checkbox()),
            (new column('us_firstname', 'LBL_NAME', 5))
                ->setTemplate('{{ formatName(val, row.us_lastname) }}'),
            (new column('us_role', 'LBL_ROLE', 2))
                ->setTemplate('{{ userRole(val)|raw }}')
                ->addClass('text-center'),
            (new column('us_last_login', 'LBL_LAST_LOGIN', 2))
                ->setTemplate('{{ _date(val, 5) }}')
                ->addClass('text-center'),
            new columnHidden('us_lastname'),
            new columnHidden('us_id')
        );
	}

	public function sendPassword($keyFields){
		$sql = "SELECT us_password_sent, us_email FROM " . DB_NAME_WEB . ".users WHERE us_uf_id = '" . $this->owner->user->getGroupId() . "' AND us_id = '" . (int) $keyFields['us_id'] . "' LIMIT 1";
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
			$this->owner->pageRedirect('/my-users/');
		}
	}
}
