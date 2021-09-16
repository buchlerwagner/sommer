<?php
class customersTable extends table {

	public function setup() {
		$this->dbTable = 'users';
		$this->keyFields = ['us_id'];
        $this->where = 'us_group = "' . USER_GROUP_CUSTOMERS . '" AND us_shop_id = ' . $this->owner->shopId;

		$this->subTable = true;
		$this->delete = true;
		$this->header = true;
		$this->view = true;
		$this->modalSize = 'lg';
		$this->deleteField = 'us_deleted';
		$this->additionalOptionsTemplate = 'table_options_user';

		$this->settings['display']    = 10;
		$this->settings['orderfield'] = 'us_lastname ASC, us_firstname';
		$this->settings['orderdir']   = 'asc';

        $this->formName = 'editCustomer';

        $this->addColumns(
            new column('us_enabled', 'LBL_ENABLED_SHORT', 1, enumTableColTypes::Checkbox()),
            (new column('us_firstname', 'LBL_NAME', 5))->setTemplate('{{ formatName(val, row.us_lastname) }}'),
            (new column('us_last_login', 'LBL_LAST_LOGIN', 2))->setTemplate('{{ _date(val, 5) }}')->addClass('text-center'),
            (new column('us_last_order', 'LBL_LAST_ORDER', 2))->setTemplate('{{ _date(val, 5) }}')->addClass('text-center'),
            new columnHidden('us_lastname')
        );

        $this->addButton(
            'BTN_NEW_CUSTOMER',
            true
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

			$this->owner->pageRedirect('/customers/');
		}
	}

    public function loadRows() {
        $this->settings['filters'] = $this->getFilters();
        parent::loadRows();
    }

    private function getFilters(){
        $where = [];

        $filterValues = $this->getSession('customerFilters');

        if (!empty($filterValues)) {
            $this->showDeletedRecords = true;

            foreach ($filterValues as $field => $values) {
                if (empty($values)) {
                    continue;
                }
                if (is_array($values)) {
                    foreach ($values as $key => $val) {
                        $values[$key] = $this->owner->db->escapestring($val);
                    }
                } else {
                    $values = $this->owner->db->escapestring($values);
                }
                switch ($field) {
                    case 'userName':
                        $where[] = "(us_firstname LIKE '%" . $values . "%' OR us_lastname LIKE '%" . $values . "%' OR CONCAT(us_lastname, ' ', us_firstname) LIKE '%" . $values . "%')";
                        break;

                    default:
                        $where[$field] = $field . " = '$values'";
                        break;
                }
            }

        }

        return $where;
    }
}
