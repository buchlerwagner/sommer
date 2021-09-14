<?php
class usersTable extends table {

	public function setup() {
		$this->dbTable = 'users';
		$this->join = 'LEFT JOIN user_groups ON (ug_id = us_ug_id)';
		$this->keyFields = ['us_id'];
        $this->where = 'us_group = "' . USER_GROUP_PARTNERS . '"';

		if($groups = $this->owner->user->getUserGroups()){
            $this->where .= ' AND ug_id IN (' . implode(',', $groups) . ')';
        }

		$this->subTable = false;
		$this->delete = true;
		$this->header = true;
		$this->view = true;
		$this->deleteField = 'us_deleted';
		$this->additionalOptionsTemplate = 'table_options_user';

		$this->settings['display']    = 10;
		$this->settings['orderfield'] = 'us_lastname ASC, us_firstname';
		$this->settings['orderdir']   = 'asc';

        $this->formName = 'editUser';

        $this->addColumns(
            new column('us_enabled', 'LBL_ENABLED_SHORT', 1, enumTableColTypes::Checkbox()),
            (new column('us_firstname', 'LBL_NAME', 4))->setTemplate('{{ formatName(val, row.us_lastname) }}'),
            new column('ug_name', 'LBL_GROUP', 2),
            (new column('us_role', 'LBL_ROLE', 1))->setTemplate('{{ userRole(val)|raw }}')->addClass('text-center'),
            (new column('us_last_login', 'LBL_LAST_LOGIN', 2))->setTemplate('{{ _date(val, 5) }}')->addClass('text-center'),
            new columnHidden('us_lastname')
        );

        if($this->owner->user->hasFunctionAccess('users-add')) {
            $this->addButton(
                'BTN_NEW_USER',
                true,
                [
                    'form' => 'addUser'
                ]
            );
        }
	}

	public function onAfterDelete($keyFields, $real = true) {
		$sql = "SELECT us_email FROM " . DB_NAME_WEB . ".users WHERE us_id = '" . $keyFields['us_id'] . "'";
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

        /**
         * Unlink assigned cars from user
         */

        /**
         * @var $car carData
         */
        $car = $this->owner->addByClassName('carData');

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'user_fleet',
                [
                    'uf_id',
                    'uf_f_id',
                ],
                [
                    'uf_us_id' => $keyFields['us_id']
                ]
            )
        );
        if($result){
            foreach($result AS $row){
                $car->init($row['uf_f_id']);

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'user_fleet',
                        [
                            'uf_mileage_end' => $car->getMileage(),
                            'uf_end' => 'NOW()',
                            'uf_revoked' => 1,
                        ],
                        [
                            'uf_id' => $row['uf_id'],
                            'uf_f_id' => $row['uf_f_id']
                        ]
                    )
                );
            }
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

			$this->owner->pageRedirect('/partners/users/');
		}
	}

    public function loadRows() {
        $this->settings['filters'] = $this->getFilters();
        parent::loadRows();
    }

	public function onAfterLoad() {
		if($this->rows){
			foreach($this->rows AS $key => $row){
				$this->rows[$key]['options'] = [
					'delete' => $this->hasHigherRole($row[0], $row[4])
				];
			}
		}
	}

	public function isDeleteable($keyFields) {
		$deleteAble = true;

        $sql = "SELECT us_id, us_role FROM " . DB_NAME_WEB . ".users WHERE us_id = '" . (int) $keyFields['us_id'] . "' LIMIT 1";
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

    private function getFilters(){
        $where = [];

        $filterValues = $this->getSession('userFilters');

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
