<?php
class user extends ancestor {
    const MEMCACHE_KEY = 'cl-user-profile-';

	public $id = 0;
	public $group = false;
	public $role = false;
	public $loadUserData = true;

	private $loggedIn = false;
	private $data = [];
    private $domain;

	public function init() {
		$this->getUserSession();
		$this->setDomain($this->owner->domain);
		return $this;
	}

	public function setId($id){
		$this->id = (int) $id;
		return $this;
	}

	public function setDomain($domain){
	    $domain = rtrim($domain, '/');
        if(strpos($domain, 'http') === false){
            $domain = 'https://' . $domain;
        }

        $this->domain = $domain . '/';
        return $this;
    }

	public function setUserId($userId){
        $this->id = (int) $userId;

		if($this->loadUserData) {
			$this->getUserSession();
			if ($this->data['id'] != $userId) {
                $this->id = (int) $userId;

				$this->loadUser();
			}
		}else{
			$this->data = [];
		}

		return $this;
	}

	public function getUser(){
		return ($this->data ? $this->data : false);
	}

    public function clearUserDataCache($userId){
        $this->owner->mem->delete(self::MEMCACHE_KEY . (int) $userId);
    }

    public function getUserProfile($userId){
        $user = false;

        if($userId) {
            $user = $this->owner->mem->get(self::MEMCACHE_KEY . (int) $userId);
            if (!$user) {
                $row = $this->owner->db->getFirstRow(
                    "SELECT * 
					    FROM " . DB_NAME_WEB . ".users
					        WHERE us_deleted = 0 AND us_id='" . (int)$userId . "' LIMIT 1"
                );
                if(!$row) {
                    $user['id'] = (int) $userId;
                    $user['missing'] = true;
                }else{
                    unset(
                        $row['us_password'],
                        $row['us_force_pwchange'],
                        $row['us_password_set'],
                        $row['us_password_sent'],
                        $row['us_enabled'],
                        $row['us_deleted'],
                        $row['us_newsletter'],
                        $row['us_hash']
                    );

                    foreach ($row as $key => $val) {
                        $subKey = substr($key, 3);
                        $user[$subKey] = $val;
                    }

                    $user['name'] = localizeName($user['firstname'], $user['lastname'], $this->owner->language);;
                    $user['img'] = $this->setProfilePicture($user);
                }

                $this->owner->mem->set(self::MEMCACHE_KEY . $user['id'], $user);
            }
        }

        return $user;
    }

	public function clearSession(){
		$this->data = [];
		$this->owner->delSession(SESSION_USER);
	}

	public function getGroup(){
		return $this->data['group'];
	}

    public function getGroupId(){
        return (int) $this->data['ug_id'];
    }

	public function getRole(){
		return $this->data['role'];
	}

	public function setPassword($password, $updateUserSession = false){
		if($password) {
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					DB_NAME_WEB . ".users",
					[
						'us_password' => password_hash($password, PASSWORD_DEFAULT),
						'us_password_set' => 1,
						'us_force_pwchange' => 0
					],
					['us_id' => $this->id]
				)
			);

			if($updateUserSession) {
				$this->data['password_set'] = 1;
				$this->data['force_pwchange'] = false;
				$this->setUserSession();
			}
		}

		return $this;
	}

	public function setGroup($group){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                DB_NAME_WEB . ".users",
                [
                    'us_group' => $group,
                ],
                ['us_id' => $this->id]
            )
        );
        return $this;
    }

    public function setRole($role){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                DB_NAME_WEB . ".users",
                [
                    'us_role' => $role,
                ],
                ['us_id' => $this->id]
            )
        );
        return $this;
    }

    public function setEnabled($enabled = true){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                DB_NAME_WEB . ".users",
                [
                    'us_enabled' => ($enabled)
                ],
                ['us_id' => $this->id]
            )
        );
        return $this;
    }

	/**
	 * Validate user password
	 * Moved from function lib checkUserPassword()
	 * @param string $password
	 * @param bool|string $email
	 * @return bool
	 */
	public function validatePassword($password, $email = false){
		$valid = false;
		$db = $this->owner->db;

		$user = $db->getFirstRow(
			"SELECT us_password FROM " . DB_NAME_WEB . ".users WHERE us_deleted = 0 AND us_enabled = 1 AND us_id = '" . $db->escapestring($this->id) . "'" . ($email ? ' AND us_email="' . $db->escapestring($email) . '"' : '')
		);
		if($user && password_verify($password, $user['us_password'])){
			$valid = true;
		}

		return $valid;
	}

	public function validateUserByEmail($email){
		$result = [
			'valid' => false
		];
		$db = $this->owner->db;

		$user = $db->getFirstRow(
			"SELECT us_id FROM " . DB_NAME_WEB . ".users WHERE us_deleted = 0 AND us_enabled = 1 AND us_email = '" . $db->escapestring($email) . "'"
		);

		if($user){
			$result = [
				'valid' => true,
				'userid' => $user['us_id'],
			];
		}

		return $result;
	}

	public function validateUserByUUID($uuid){
		$result = false;
		$db = $this->owner->db;

		$user = $db->getFirstRow(
			"SELECT us_id FROM " . DB_NAME_WEB . ".users WHERE us_deleted = 0 AND us_enabled = 1 AND us_hash = '" . $db->escapestring($uuid) . "'"
		);

		if($user){
			$result = (int) $user['us_id'];
		}

		return $result;
	}

	/**
	 * @param $email
	 * @param $password
	 * @param $userGroup
	 * @return array
	 */
	public function login($email, $password, $userGroup = false){
        $this->data = false;
		$db = $this->owner->db;

		$user = $db->getFirstRow(
			"SELECT us_id, us_password FROM " . DB_NAME_WEB . ".users
			    WHERE us_deleted = 0 AND us_enabled = 1 AND us_email = '" . $db->escapestring($email) . "'" . ($userGroup ? " AND us_group = '" . $userGroup . "'" : '')
		);

		if (!empty($user) && password_verify($password, $user['us_password'])) {
		    $this->loginWithId($user['us_id']);
		}

        return $this->data;
	}

	public function loginWithId($id){
        $this->data = false;
        $db = $this->owner->db;

        $this->setId($id)->loadUser();
        $needAuthentication = $this->checkLoginHistory();

        if (!empty($this->data)) {
            $db->sqlQuery(
                $db->genSQLUpdate(
                    DB_NAME_WEB . ".users",
                    [
                        'us_last_login' => 'NOW()'
                    ],
                    [
                        'us_id' => $this->data['id']
                    ]
                )
            );

            $this->setUserSession();

            //$this->loginCookie($this->data['hash'], $permanent, $social);
            $this->owner->setSession(SESSION_LOCALE, $this->data['language']);
        }

        return $this->data;
    }

	public function checkLoginHistory($userId = false){
		if(!$userId){
			$userId = $this->id;
		}

		$need2Fa = false;
		$isMobile = false;

		//$isMobile = $this->isMobileView();
		$ipData = $this->owner->lib->getLocationByIp();
		$browser = get_browser(NULL, true);
		$hash = md5($browser['platform'] . $browser['browser'] . $ipData['country_code'] . $ipData['ip'] . $this->owner->machineId);

		$sql = "SELECT * FROM " . DB_NAME_WEB . ".user_logins WHERE ul_us_id='" . $userId . "' AND ul_hash='" . $hash . "' AND ul_expire >= NOW()";
		$row = $this->owner->db->getFirstRow($sql);
		if($row) {
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					DB_NAME_WEB . '.user_logins',
					[
						'ul_last_used' => 'NOW()',
						'ul_logins' => 'INCREMENT',
					],
					[
						'ul_us_id' => $userId,
						'ul_hash' => $hash
					]
				)
			);
		}else{
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLInsert(
					DB_NAME_WEB . '.user_logins',
					[
						'ul_us_id' => $userId,
						'ul_hash' => $hash,
						'ul_ip' => $ipData['ip'],
						'ul_country' => $ipData['country_code'],
						'ul_mobile' => ($isMobile ? 1 : 0),
						'ul_browser' => $browser['browser'],
						'ul_browser_ver' => $browser['version'],
						'ul_os' => $browser['platform'],
						'ul_last_used' => 'NOW()',
						'ul_expire' => date('Y-m-d H:i:s', time() + (60 * 60 * 24 * 365)),
						'ul_logins' => 1,
						'ul_validated' => 0
					],
					['ul_us_id', 'ul_hash']
				)
			);

			$need2Fa = $hash;
		}

		return $need2Fa;
	}

	private function createLoginToken($type = 'NEWPWD', $expire = 8){
		$token = generateRandomString(50, false);
		$expiry = time() + (60 * 60 * $expire);
		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				DB_NAME_WEB . ".tokens",
				[
					'tk_id' => $token,
					'tk_us_id' => $this->id,
					'tk_expire' => date('Y-m-d H:i:s', $expiry),
					'tk_type' => $type,
					'tk_used' => 0,
				]
			)
		);
		return $token;
	}

	/**
	 * Create pwd change token link
	 * @param mixed $type NEWPWD|REGISTER|LOGIN
	 * @param mixed $page default: 'set-new-password'
	 * @param int $expiry hours
	 * @return string
	 */
	public function getPasswordChangeLink($type = false, $page = false, $expiry = 8){
		if(!$type){
			$type = 'NEWPWD';
		}

		if(!$page){
			$page = 'set-new-password';
		}

		return $this->domain . $page . '/?token=' . urlencode($this->createLoginToken($type, $expiry));
	}

	public function checkToken($token, $setUsed = false){
		$valid = [
			'valid' => false
		];
		$token = urldecode($token);
		$sql = "SELECT * FROM " . DB_NAME_WEB . ".tokens WHERE tk_id='" . $this->owner->db->escapeString($token) . "' AND tk_expire > NOW() AND tk_used = 0";
		$res = $this->owner->db->getFirstRow($sql);
		if($res){
			$valid = [
				'valid' => true,
				'userid' => $res['tk_us_id'],
				'token' => $res['tk_id'],
				'type' => $res['tk_type'],
				'expire' => $res['tk_expire']
			];

			if($setUsed){
				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLUpdate(
						DB_NAME_WEB . ".tokens",
						[
							'tk_used' => 1
						],
						[
							'tk_id' => $res['tk_id'],
							'tk_us_id' => $res['tk_us_id']
						]
					)
				);
			}
		}

		return $valid;
	}

	private function loadUser(){
		$this->data = [];
		if($this->id){
			$db = $this->owner->db;
			$user = $db->getFirstRow(
				"SELECT * FROM " . DB_NAME_WEB . ".users
					WHERE us_deleted = 0 AND us_enabled = 1 AND us_id='" . $this->id . "'"
			);

			if (!empty($user)) {
				unset(
					$user['us_password']
				);

				foreach ($user as $key => $val) {
					$prefix = substr($key, 0, 2);
					$subKey = substr($key, 3);

					switch($prefix){
						case 'us':
						default:
							$key = 'user';
							break;
					}

					$this->data[$subKey] = $val;
				}

				$this->group = $this->data['group'];
				$this->role = $this->data['role'];

                $this->data['name'] = localizeName($this->data['firstname'], $this->data['lastname'], $this->owner->language);
                $this->data['img'] = $this->setProfilePicture($this->data);
				$this->data['timezone'] = $this->getTimezone($this->data['timezone']);

				$this->data['access_rights'] = $this->getAccessLevels(
					$this->group,
					$this->role
				);

				$this->data['function_rights'] = $this->getFunctionRights(
					$this->group,
					$this->role
				);

                if($this->role === USER_ROLE_FLEET_ADMIN){
                    $this->data['user_groups'] = $this->getUserGroups();
                }else{
                    $this->data['user_groups'] = false;
                }
			}
		}

		return $this;
	}

	private function getAccessLevels($userGroup, $userRole){
		$rights = [];
		$accessRights = $this->owner->db->getRows(
			"SELECT al_page, al_right FROM " . DB_NAME_WEB . ".access_levels 
				WHERE al_group = '" . $this->owner->db->escapeString($userGroup) . "' AND al_role ='" . $this->owner->db->escapeString($userRole) . "'"
		);
		if($accessRights) {
			foreach ($accessRights as $row) {
				if (empty($rights[$row['al_page']]) || $rights[$row['al_page']] < $row['al_right']) {
					$rights[$row['al_page']] = $row['al_right'];
				}
			}
		}

		return $rights;
	}

	private function getFunctionRights($userGroup, $userRole){
		$rights = [];

		$functionRights = $this->owner->db->getRows(
			"SELECT afr_key FROM " . DB_NAME_WEB . ".access_function_rights 
				WHERE afr_group = '" . $this->owner->db->escapeString($userGroup) . "' AND afr_role ='" . $this->owner->db->escapeString($userRole) . "'"
		);
		if($functionRights) {
			foreach ($functionRights as $row) {
				$rights[] = $row['afr_key'];
			}
		}

		return $rights;
	}

    public function changeUserSessionData($key, $value){
        if(isset($this->data[$key])){
            $this->data[$key] = $value;
        }

        $this->owner->setSession(SESSION_USER, $this->data);
    }

	private function setUserSession(){
		$this->owner->setSession(SESSION_USER, $this->data);
	}

	private function getUserSession(){
		$this->data = $this->owner->getSession(SESSION_USER);
		if($this->data) {
			$this->id = $this->data['id'];
			$this->group = $this->data['group'];
			$this->role = $this->data['role'];

			$this->isLoggedIn();
		}

		return $this;
	}

	public function isLoggedIn(){
		if(!Empty($this->data) && $this->data['id'] === $this->id){
			$this->loggedIn = true;
		}
		return $this->loggedIn;
	}

	/**
	 * @param string $page
	 * @param int $level
	 * @return bool
	 */
	public function hasPageAccess($page, $level = ACCESS_RIGHT_WRITE){
		return (!empty($this->data['access_rights'][$page])
			&& $this->data['access_rights'][$page] >= $level);
	}

	public function getAccessLevel($page){
		return (isset($this->data['access_rights'][$page]) ? $this->data['access_rights'][$page] : ACCESS_RIGHT_NO);
	}

	/**
	 * @param string $function
	 * @return bool
	 */
	public function hasFunctionAccess($function){
		$out = false;

		if($this->data['function_rights']){
			if(in_array($function, $this->data['function_rights'])){
				$out = true;
			}
		}

		return $out;
	}

	/**
	 * Checks weather the user is logged in with the given role
	 * @param string|array $roles
	 * @return bool
	 */
	public function hasRole($roles){
		$out = false;

		if (!is_array($roles)) {
			$roles = [$roles];
		}

		if(in_array($this->data['role'], $roles)) {
			$out = true;
		}

		return $out;
	}


	/**
	 * Checks weather the user is logged in with the given type
	 * @param string|array $types
	 * @return bool
	 */
	public function hasGroup($types){
		$out = false;

		if (!is_array($types)) {
			$types = [$types];
		}

		if(in_array($this->data['group'], $types)) {
			$out = true;
		}

		return $out;
	}

	private function getTimezone($timeZone){
		$out = [];
		$timezone = $this->owner->db->getFirstRow(
			"SELECT * FROM " . DB_NAME_WEB . ".timezones WHERE tz_id = '" . (int) $timeZone . "'"
		);

		if (!empty($timezone)) {
			foreach ($timezone as $key => $val) {
				$out[substr($key, 3)] = $val;
			}
		}

		return $out;
	}

	private function setProfilePicture($data){
        if($data['img']){
            $src = FOLDER_UPLOAD . 'profiles/' . $data['img'];
        }else{
            if(Empty($data['title'])) $data['title'] = 'MR';
            $src = '/images/' . strtolower($data['title']) . '.svg';
        }

        return $src;
    }

    public function deleteUser($userId){
        $success = true;

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLDelete(
                DB_NAME_WEB . '.users',
                [
                    'us_id' => $userId
                ]
            )
        );

        return $success;
    }

    public function getUserGroups($id = false){
	    $out = [];

	    if(!$id){
	        $id = $this->id;
        }

        $results = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'user_assigned_groups',
                [
                    'uag_ug_id'
                ],
                [
                    'uag_us_id' => $id
                ]
            )
        );
        if ($results) {
            foreach($results AS $row){
                $out[] = $row['uag_ug_id'];
            }
        }

        return $out;
    }

    public function setUserGroups($groupIds = [], $id = false){
        if(!$id){
            $id = $this->id;
        }

        $this->deleteUserGroups($id);

        if(!Empty($groupIds)){
            foreach($groupIds AS $groupId) {
                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLInsert(
                        'user_assigned_groups',
                        [
                            'uag_ug_id' => $groupId,
                            'uag_us_id' => $id
                        ]
                    )
                );
            }
        }

    }

    public function deleteUserGroups($id = false){
        if(!$id){
            $id = $this->id;
        }
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLDelete(
                'user_assigned_groups',
                [
                    'uag_us_id' => $id
                ]
            )
        );
    }

    public function getNotificationOptions($event = false): array {
        $options = [];

        $sql = "SELECT * FROM " . DB_NAME_WEB . ".property_types 
					LEFT JOIN " . DB_NAME_WEB . ".user_notifications ON (prop_id = un_prop_id AND un_us_id = " . $this->id . ")
						WHERE (prop_notification_changeable = 1 OR prop_email_changeable = 1 OR prop_sms_changeable = 1) AND prop_has_notification = 1";
        $sql .= ' AND JSON_CONTAINS(prop_notification_scope, \'"' . $this->role .  '"\', \'$.' . $this->group . '\')';

        if($event){
            $sql .= " AND prop_event = '" . $this->owner->db->escapeString($event) . "'";
        }

        $sql .= " ORDER BY prop_category";

        $results = $this->owner->db->getRows($sql);
        if ($results) {
            foreach ($results AS $row) {
                $options[$row['prop_id']] = [
                    'id' => $row['prop_id'],
                    'category' => $row['prop_category'],
                    'title' => $row['prop_notification_title'],
                    'level' => $row['prop_level'],
                    'notification' => [
                        'value' => (($row['un_notification'] !== NULL && $row['prop_notification_changeable']) ? $row['un_notification'] : $row['prop_notification']),
                        'change' => (bool) $row['prop_notification_changeable'],
                        'allow' => (bool) $row['prop_notification'],
                    ],
                    'email' => [
                        'value' => (($row['un_email'] !== NULL && $row['prop_email_changeable']) ? $row['un_email'] : $row['prop_email']),
                        'change' => (bool) $row['prop_email_changeable'],
                        'allow' => (bool) $row['prop_email'],
                    ],
                    'sms' => [
                        'value' => (($row['un_sms'] !== NULL && $row['prop_sms_changeable']) ? $row['un_sms'] : $row['prop_sms']),
                        'change' => (bool) $row['prop_sms_changeable'],
                        'allow' => (bool) $row['prop_sms'],
                    ],

                ];

            }
        }

        return ($event ? $options[$event] : $options);
    }
}
