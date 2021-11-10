<?php
class functions extends ancestor {

	/**
	 * Collect list for selects
	 * @param string $name
	 * @param array $params
	 * @return array
	 */
	public function getList($name, $params = []){
		$list = [];

		$sql = '';
		$translate = false;
		$orderByListValue = false;

		switch($name) {
			case 'userGroups':
				$translate = false;
				if ($params['empty']) $list = [0 => '---'] + $list;

				foreach($GLOBALS['USER_GROUPS'] AS $key => $value){
					$list[$key] = [
						'name' => $this->owner->translate->getTranslation($value['label']),
						'class' => 'alert-' . $value['color']
					];

				}
				break;
			case 'roles':
				$translate = false;
				if($params['empty']) $list = [0 => '---'] + $list;
                if(Empty($params['group'])) $params['group'] = USER_GROUP_ADMINISTRATORS;

				$userLevel = 0;
				$level = 0;
				if ($params['limit']) {
					foreach($GLOBALS['USER_ROLES'][$params['group']] AS $key => $value){
						if($params['limit'] == $key){
							$userLevel = $level;
							break;
						}
						$level++;
					}
				};

				$level = 0;
				foreach($GLOBALS['USER_ROLES'][$params['group']] AS $key => $value){
					if($userLevel <= $level) {
						$list[$key] = [
							'name' => $this->owner->translate->getTranslation($value['label']),
							'class' => 'badge-' . $value['color']
						];
					}
					$level++;
				}
				break;

			case 'users':
				if($params['empty']) $list[0] = '---';
				$sql = "SELECT us_id as list_key, CONCAT(us_lastname, ' ', us_firstname) as list_value 
							FROM " . DB_NAME_WEB . ".users
								WHERE us_group = '" . (int) $params['group'] . "' AND us_enabled = 1 AND us_deleted = 0";
				if($params['roles']){
					if(!is_array($params['roles'])){
						$params['roles'] = [$params['roles']];
					}

					$tmp = [];
					foreach($params['roles'] AS $role){
						$tmp[$role] = "'" . $role . "'";
					}

					$sql .= " AND us_role IN (" . implode(',', $tmp) . ")";
				}

				$sql .= " ORDER BY list_value ASC";

				break;

			case 'access_functions':
				$sql = "SELECT af_page AS list_key, af_name AS list_value, af_key FROM " . DB_NAME_WEB . ".access_functions ORDER BY af_name";

				$res = $this->owner->db->getRows($sql);
				if (!empty($res)) {
					$i = 0;
					$key = false;
					foreach($res as $row) {
						if($key != $row['list_key']){
							$key = $row['list_key'];
							$i = 0;
						}
						$list[$row['list_key']][$i]['name'] = $row['list_value'];
						$list[$row['list_key']][$i]['key'] = $row['af_key'];
						$i++;
					}
				}
				$sql = '';
				break;
		}

		if (!empty($sql)) {
			$res = $this->owner->db->getRows($sql);
			if (!empty($res)) {
				$i = 0;
				if($params['empty'] && $params['json']){
					$list[$i] = [
						'id' => 0,
						'text' => '---',
					];
					$i++;
				};
				foreach($res as $row) {
					if($params['json']){
						$list[$i] = [
							'id' => $row['list_key'],
							'text' => $row['list_value'],
						];
						if(!Empty($row['list_group'])){
							$list[$i]['groupId'] = $row['list_group_id'];
							$list[$i]['groupName'] = $row['list_group'];
						}
						if(!Empty($row['list_subtext'])){
							$list[$i]['data']['subtext'] = $row['list_subtext'];
						}
						if(!Empty($row['list_tokens'])){
							$list[$i]['data']['tokens'] = $row['list_tokens'];
						}
					}else {
						$list[$row['list_key']] = $row['list_value'];
					}
					$i++;
				}
			}
		}

		if ($translate) {
			foreach($list as $key => $val) {
				if (empty($val)) continue;
				if (is_array($val)) {
					foreach($val as $key2 => $val2) {
						$list[$key][$key2] = $this->owner->translate->getTranslation($val2);
					}
				} else {
					$list[$key] = $this->owner->translate->getTranslation($val);
				}
			}
		}

		if ($orderByListValue) {
			asort($list);
		}

		return $list;
	}

	/**
	 * Get localized settings from GLOBAL var, defined in constant.php
	 *
	 * @return mixed
	 */
	public function getLocaleSettings(){
		if($this->owner->language AND isset($GLOBALS['REGIONAL_SETTINGS'][$this->owner->language])){
			return $GLOBALS['REGIONAL_SETTINGS'][$this->owner->language];
		}else{
			return $GLOBALS['REGIONAL_SETTINGS']['default'];
		}
	}

	/**
	 * Custom date formatting function
	 *
	 * @param string $date date to convert (YYYY-MM-DD HH:II:SS or timestamp())
	 * @param int $view the type of display format:
	 *   1: 2014. jan. 01.
	 *  10: 2014. január 1.
	 *  11: 2014. január 1
	 *  12: 2014. január
	 *   2: 2014. jan. 01., Mon.
	 *   3: jan. 01.
	 *  32: 01.01.
	 *   4: jan. 01., Mon.
	 *  41: january 01., Monday
	 *   5: 2014. jan. 01. 12:34
	 *   6: 12:34
	 *
	 * @param bool $addTimezone whether or not to add timezone
	 * @return string
	 */
	public function formatDate($date, $view = 0, $addTimezone = true){
		if(Empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return '';

		$out = '';
		$formats = $this->getLocaleSettings();

		if(!is_numeric($date)){
			$date = str_replace(['/', '.'], '-', trim($date, '.'));
			$dt = strtotime($date);
		}else{
			$dt = $date;
		}

		if($addTimezone && $this->owner->user->getUser()['timezone']['code'] && date('His', $dt)>0){
			try {
				$datetime = new DateTime(date('Y-m-d H:i:s', $dt));
				$tz = new DateTimeZone($this->owner->user->getUser()['timezone']['code']);
				$date2 =  $datetime->setTimezone($tz)->format('Y-m-d H:i:s');
				$dt = strtotime($date2);
			}catch (Exception $e){
			}
		}

		$_months = [];
		$_monthsShort = [];
		$_days = [];
		$_daysShort = [];

		for($i=1;$i<=12;$i++){
			$_months[$i] = $this->owner->translate->getTranslation('LBL_MONTH_'.$i);
			$_monthsShort[$i] = $this->owner->translate->getTranslation('LBL_MONTH_SHORT_'.$i);

			if($i<=7){
				$_days[$i] = $this->owner->translate->getTranslation('LBL_DAY_'.$i);
				$_daysShort[$i] = $this->owner->translate->getTranslation('LBL_DAY_SHORT_'.$i);
			}
		}
		$_days[0] = $_days[7];
		$_daysShort[0] = $_daysShort[7];

		$item['m'] = date('n', $dt);
		$item['mm'] = date('m', $dt);
		$item['M'] = $_monthsShort[date('n', $dt)];
		$item['MM'] = $_months[date('n', $dt)];
		$item['d'] = date('j', $dt);
		$item['dd'] = date('d', $dt);
		$item['D'] = $_daysShort[date('w', $dt)];
		$item['DD'] = $_days[date('w', $dt)];
		$item['y'] = date('y', $dt);
		$item['yy'] = date('Y', $dt);

		if($formats['timeformat'] = 24){
			$item['time'] = date('H:i', $dt);
		} else {
			$item['time'] = date('h:i A', $dt);
		}

		switch($view){
			case 1: # 2014. jan. 01.
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'].'. ';
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['M'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['dd'].'. ';
					}
				}
				break;
			case 10: # 2014. január 1.
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'].'. ';
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['MM'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['d'].'. ';
					}
				}
				break;
			case 11: # 2014. január 1
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'].'. ';
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['MM'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['d'];
					}
				}
				break;
			case 12: # 2014. január
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'].'. ';
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['MM'].' ';
					}
				}
				break;
			case 2: # 2014. jan. 01., Mon.
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'].'. ';
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['M'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						if($i==0) $out .= $item['D'].', ';
						$out .= $item['dd'].'. ';
						if($i==2) $out .= $item['D'];
					}
				}
				break;
			case 3:	# jan. 01.
			case 31:
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['M'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['dd'].'. ';
					}
				}
				break;
			case 32:	# 01.01.
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['mm'].'.';
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['dd'].'.';
					}
				}
				break;

			case 4: # jan. 01., Mon.
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['M'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						if($i==0) $out .= $item['D'].', ';
						$out .= $item['dd'].'. ';
						if($i==2) $out .= $item['D'];
					}
				}
				break;

			case 41: # january 01., Monday
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['MM'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						if($i==0) $out .= $item['DD'].', ';
						$out .= $item['dd'].'. ';
						if($i==2) $out .= $item['DD'];
					}
				}
				break;

			case 5: # 2014. jan. 01. 12:34
				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'].'. ';
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['M'].' ';
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['dd'].'. ';
					}
				}

				$out .= ' ' . $item['time'];

				break;

			case 6: # 12:34

				$out .= $item['time'];
				break;

			default:
				$tmp = str_replace(['d', 'D', 'm', 'M', 'y', 'Y'], '', $formats['dateformat']);

				for($i=0;$i<3;$i++){
					if($formats['dateorder'][$i]=='y'){
						$out .= $item['yy'];
					}
					if($formats['dateorder'][$i]=='m'){
						$out .= $item['mm'];
					}
					if($formats['dateorder'][$i]=='d'){
						$out .= $item['dd'];
					}
					$out .= $tmp{$i};
				}
				break;
		}

		return trim($out);
	}

	public function convertToUTCTime($timeStamp, $originalTimeZone, $format = 'Y-m-d H:i:s'){
		$datetime = new DateTime(date('Y-m-d H:i:s', $timeStamp), new DateTimeZone($originalTimeZone));
		$tz = new DateTimeZone(SERVER_TIME_ZONE);
		return $datetime->setTimezone($tz)->format($format);
	}

	/**
	 * Get time in textual
	 *
	 * @param string $label
	 * @param bool $responsive
	 * @return mixed|string
	 */
	public function getTimeText($label, $responsive = false) {
		$result = $this->owner->translate->getTranslation($label);
		if ($responsive) {
			$result = mb_substr($result, 0, 1, 'UTF-8') . '<span class="hidden-xs hidden-sm">' . mb_substr($result, 1, null, 'UTF-8') . '</span>';
		}
		return $result;
	}

	/**
	 * Time formatting function
	 *
	 * @param string $time
	 * @param int $mode
	 * @return string
	 */
	public function formatTime($time, $mode = 0){
		$out = '';

		if ($mode == 2) {
			$h = floor ($time / 60);
			$m = round(($time - ($h * 60)) / 15);
			switch($m) {
				case 1:
					$h .= '¼';
					break;
				case 2:
					$h .= '½';
					break;
				case 3:
					$h .= '¾';
					break;
			}

			$out .= $h.' '.$this->getTimeText('LBL_HOUR', false);
		} else {
			$d = floor ($time / 1440);
			$h = floor (($time - $d * 1440) / 60);
			$m = $time - ($d * 1440) - ($h * 60);

			//if($h<10) $h = '0'.$h;
			//if($m<10) $m = '0'.$m;
			//if($d>0)  $h += 24;
			$responsive = ($mode == 1);
			if($d>0) $out .= $d.' '.$this->getTimeText('LBL_DAY', $responsive).' ';
			if($h>0) $out .= $h.' '.$this->getTimeText('LBL_HOUR', $responsive).' ';
			if($m>0) $out .= $m.' '.$this->getTimeText('LBL_MIN', $responsive);
		}

		return $out;
	}


	public function formatDayOfWeek($dow){
		$out = '';
		$dow = explode('|', trim($dow, '|'));
		if(is_array($dow)) {
			$tmp = [];
			foreach ($dow AS $day) {
				array_push($tmp, $this->owner->translate->getTranslation('LBL_DAY_SHORT_' . $day));
			}

			$out = implode('/', $tmp);
		}
		return $out;
	}

	/**
	 * Format price to localized format
	 *
	 * @param double $price
	 * @param bool $currency
	 * @param bool $displaycurrency whaether or not to add the currency at the end of the string
	 * @return string
	 */
	public function formatPrice($price, $currency = false, $displaycurrency = true){
		$formats = $this->getLocaleSettings();

		$out = number_format($price, $formats['currency_round'], $formats['decimal_point'], $formats['thousand_sep']);
		$currencies = $this->owner->lists->getCurrencies();

		if($currency && $displaycurrency) {
			if(!Empty($currencies[$currency])) $currency = $currencies[$currency];
			$out .= ' ' . $currency;
		}

		return $out;
	}

	/**
	 * Convert time format (hh:ii) to number (XX min)
	 *
	 * @param string $time
	 * @param int $unit
	 * @return float
	 */
	public function timeToNumber($time, $unit = 5) {
		$time = explode(':', $time);
		$minutes = 0;
		switch(count($time)) {
			case 1:
				$minutes = $time[0];
				break;
			case 2:
			case 3:
				$minutes += $time[0] * 60;
				$minutes += $time[1];
				break;
		}
		if (empty($unit)) $unit = 1;
		return round($minutes / $unit);
	}

	/**
	 * Convert number (XX sec/min) to time format (hh:ii)
	 *
	 * @param int $number
	 * @param int $unit
	 * @return string
	 */
	public function numberToTime($number, $unit = 5) {
		$time = '';
		$minutes = $number * $unit;
		$hours = floor($minutes / 60);
		if ($hours > 0) {
			if ($hours < 10) $time .= '0';
			$time .= $hours . ':';
			$minutes -= $hours * 60;
		} else {
			$time .= '00:';
		}
		if ($minutes < 10) $time .= '0';
		$time .= $minutes;
		return $time;
	}

	/**
	 * Convert date format (yyyy-mm-dd) to number (XX days)
	 *
	 * @param string $date
	 * @return int
	 */
	public function dateToDay($date) {
		$date = str_replace(['/', '.'], '-', trim($date, '.'));
		$dt = strtotime($date);
		$day = round(date('U', $dt) / 3600 / 24);
		return $day;
	}

	/**
	 * Convert number (XX days) to date format (yyyy-dd-mm)
	 *
	 * @param int $day
	 * @return string
	 */
	public function dayToDate($day) {
		$date = date('Y-m-d', $day * 3600 * 24);
		return $date;
	}

	/**
	 * Load accessible menus according user access
	 *
	 * @param array $menu
	 * @param bool $footer
	 * @param bool $parentKey
	 * @return array
	 */
	public function getAccessibleMenu($menu, $footer = false, $parentKey = false) {
		$result = [];
		$access_rights = (!empty($this->owner->user->getUser()['access_rights'])) ? array_keys($this->owner->user->getUser()['access_rights']) : [];

		foreach($menu as $key => $val) {
			switch ($val['display']) {
				case 0: // invisible
					continue;
					break;
				case 1: // visible
				case 2: // menu group
				case 3: // footer
					if (empty($footer) && in_array($val['display'], [3, 4])) continue;
					if (!empty($footer) && in_array($val['display'], [1, 2]) && empty($val['footer'])) continue;

					if (!empty($val['items'])) {
						$val['items'] = $this->getAccessibleMenu($val['items'], false, $key);

						if (!empty($val['items']) || (!empty($val['access']) && in_array($key, $access_rights))) {
							$result[$key] = $val;
							$result[$key]['href'] = '';
							if($parentKey) $result[$key]['href'] .= $parentKey.'/';
							$result[$key]['href'] .= $key.'/';
						}

					} else {
						if (empty($val['access']) || in_array($key, $access_rights)) {
							$result[$key] = $val;
							$result[$key]['href'] = '';
							if($parentKey) $result[$key]['href'] .= $parentKey.'/';
							$result[$key]['href'] .= $key.'/';
						}
					}

					break;
                case 10: // label
                    $result[$key] = $val;
                    break;
			}
		}

		return $result;
	}

	public function getLocationByIp($ip = false){
		static $cache = [];

		if(!$ip){
			$ip = getClientIP();
		}

		if(!$cache[$ip]){
			$sql = 'SELECT * FROM ' . DB_NAME_WEB . '.ip_cache WHERE ic_ip="' . $ip . '" AND ic_expire >= NOW()';
			$row = $this->owner->db->getFirstRow($sql);
			if ($row) {
				$cache[$ip] = [
					"ip" => $ip,
					"city" => $row['ic_city'],
					"state" => $row['ic_state'],
					"country" => $row['ic_country'],
					"country_code" => $row['ic_country_code'],
				];
			} else {
				$cache[$ip] = ipInfo($ip);
				$cache[$ip]['ip'] = $ip;
				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLInsert(
						DB_NAME_WEB . '.ip_cache',
						[
							'ic_ip' => $ip,
							'ic_requested' => 'NOW()',
							'ic_expire' => date('Y-m-d H:i:s', time() + (60 * 60 * 24 * IP_CACHE_TIMEOUT)),
							'ic_request_count' => 'INCREMENT',
							'ic_country_code' => $cache[$ip]['country_code'],
							'ic_country' => $cache[$ip]['country'],
							'ic_state' => $cache[$ip]['state'],
							'ic_city' => $cache[$ip]['city'],
							'ic_lat' => $cache[$ip]['latitude'],
							'ic_lng' => $cache[$ip]['longitude'],
						],
						['ic_ip']
					)
				);
			}
		}

		return $cache[$ip];
	}

	/**
	 * Set global custom variable to db
	 *
	 * @param string $key
	 * @param $value
	 * @return void
	 */
	public function setVar($key, $value){
		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				DB_NAME_WEB . ".variables",
				[
					'var_key' => $key,
					'var_ug_id' => $this->owner->user->group,
					'var_value' => $value
				],
				[
					'var_key',
					'var_ug_id'
				]
			)
		);
	}

	/**
	 * Get global variable from db
	 *
	 * @param string $key
	 * @return string
	 */
	public function getVar($key){
		$key = $this->owner->db->escapeString($key);
		$sql = "SELECT var_value FROM " . DB_NAME_WEB . ".variables WHERE var_key='" . $key . "' AND var_ug_id='" . $this->owner->user->group . "'";
		$row = $this->owner->db->getFirstRow($sql);

		if(!$row){
			$sql = "SELECT var_value FROM " . DB_NAME_WEB . ".variables WHERE var_key='" . $key . "' AND var_ug_id='0'";
			$row = $this->owner->db->getFirstRow($sql);
		}

		if($row){
			$result = $row['var_value'];
		}else{
			$result = false;
		}

		return $result;
	}

    public function replaceValues($content, $values){
        $values['domain'] = $this->owner->domain;

        if(!Empty($values) && is_array($values)) {
            foreach ($values AS $key => $value) {
                $pattern = '/\{\{ ' . $key . ' \}\}/';
                $content = preg_replace($pattern, $value, $content);
            }
        }

        return $content;
    }

    public function getTemplate($templateName, $replaceValues = false):array{
        $out = [];

        $res = $this->owner->db->getFirstRow(
            "SELECT * FROM " . DB_NAME_WEB . ".templates WHERE mt_key = '" . $this->owner->db->escapeString($templateName) . "' AND mt_language='" . $this->owner->language . "'"
        );
        if (!empty($res)) {
            $out = [
                'title' => ($replaceValues ?  $this->replaceValues($res['mt_subject'], $replaceValues)  : $res['mt_subject']),
                'text' => ($replaceValues ?  $this->replaceValues($res['mt_body'], $replaceValues)  : $res['mt_body']),
                'tag' => $res['mt_key'],
                'template' => $res['mt_template'],
            ];
        }

        return $out;
    }

    public function setContentPageMenus(){
        $result = $this->owner->mem->get(CACHE_PAGES . $this->owner->shopId . $this->owner->language);
        if(!$result) {
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'contents',
                    [
                        'c_id',
                        'c_parent_id',
                        'c_show_in_header',
                        'c_show_in_footer',
                        'c_empty_menu',
                        'c_title',
                        'c_page_url',
                        'c_order',
                    ],
                    [
                        'c_shop_id' => $this->owner->shopId,
                        'c_deleted' => 0,
                        'c_published' => 1,
                        'c_widget' => '',
                        'c_language' => $this->owner->language,
                    ],
                    [],
                    false,
                    'c_parent_id, c_order'
                )
            );

            $this->owner->mem->set(CACHE_PAGES . $this->owner->shopId . $this->owner->language, $result);
        }

        if($result){
            $parents = [];

            foreach($result AS $row){
                $display = 0;
                $header = false;
                $footer = false;

                if($row['c_show_in_header']){
                    $display = 1;
                    $header = true;
                }

                if ($row['c_show_in_footer']) {
                    $display = 1;
                    $footer = true;
                }

                if($row['c_parent_id']){
                    $url = $parents[$row['c_parent_id']]['url'];

                    $GLOBALS['MENU'][$url]['display'] = ($parents[$row['c_parent_id']]['empty'] ? 2 : 1);

                    if(!isset($GLOBALS['MENU'][$url]['items'])) $GLOBALS['MENU'][$url]['items'] = [];
                    $GLOBALS['MENU'][$url]['items'][$row['c_page_url']] = [
                        'display' => 1,
                        'header' => false,
                        'footer' => false,
                        'pagemodel' => 'content',
                        'title' => $row['c_title'],
                    ];
                }else{
                    $parents[$row['c_id']]['url'] = $row['c_page_url'];
                    $parents[$row['c_id']]['empty'] = ($row['c_empty_menu']);

                    $GLOBALS['MENU'][$row['c_page_url']] = [
                        'display' => $display,
                        'header' => $header,
                        'footer' => $footer,
                        'pagemodel' => 'content',
                        'title' => $row['c_title'],
                        'position' => $row['c_order'],
                    ];
                }
            }
        }
    }

    public function getWidgetContents($widget){
        $content = $this->owner->mem->get(CACHE_PAGES . $this->owner->shopId . $this->owner->language . $widget);
        if(!$content) {
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'contents',
                    [
                        'c_id AS id',
                        'c_title AS title',
                        'c_widget AS widget',
                        'c_subtitle AS subtitle',
                        'c_page_img AS image',
                        'c_content AS content',
                        'c_page_title AS pageTitle',
                        'c_page_description AS pageDescription',
                        'c_page_url AS pageUrl',
                    ],
                    [
                        'c_shop_id' => $this->owner->shopId,
                        'c_deleted' => 0,
                        'c_published' => 1,
                        'c_widget' => $widget,
                        'c_language' => $this->owner->language,
                    ],
                    [],
                    false,
                    'c_order'
                )
            );

            if($result){
                $content = [];

                foreach($result AS $row){
                    $content[$row['id']] = $row;
                    if($row['image']) {
                        $content[$row['id']]['image'] = FOLDER_UPLOAD . $this->owner->shopId . '/pages/' . $row['id'] . '/' . $row['image'];
                        
                        if(!$this->owner->data['content']['image']){
                            $this->owner->data['content']['image'] = $content[$row['id']]['image'];
                        }
                    }
                }

                $this->owner->mem->set(CACHE_PAGES . $this->owner->shopId . $this->owner->language . $widget, $content);
            }
        }

        return $content;
    }

    public function getSections($sections){
        $out = [];
        if(!is_array($sections)) $sections = [$sections];
        foreach($sections AS $section){
            $widgets = $this->getWidgetContents($section);
            foreach($widgets AS $id => $widget){
                $out[$id] = $widget;
            }
        }

        if(!Empty($out)){
            uasort($out, function ($item1, $item2) {
                return $item1['order'] <=> $item2['order'];
            });
        }

        return $out;
    }

    public function setProductCategories(){
        if($GLOBALS['MENU']['products']){
            $result = $this->owner->mem->get(CACHE_CATEGORIES . $this->owner->shopId);
            if(!$result) {
                $result = $this->owner->db->getRows(
                    $this->owner->db->genSQLSelect(
                        'product_categories',
                        [
                            'cat_id',
                            'cat_title',
                            'cat_url',
                            'cat_order',
                        ],
                        [
                            'cat_shop_id' => $this->owner->shopId,
                            'cat_visible' => 1
                        ],
                        [],
                        false,
                        'cat_order'
                    )
                );

                $this->owner->mem->set(CACHE_CATEGORIES . $this->owner->shopId, $result);
            }
            if($result){
                $GLOBALS['MENU']['products']['display'] = 2;
                $GLOBALS['MENU']['products']['items'] = [];

                foreach($result AS $row){
                    $GLOBALS['MENU']['products']['items'][$row['cat_url']] = [
                        'title' => $row['cat_title'],
                        'pagemodel' => 'products',
                        'display' => 1
                    ];
                }
            }
        }
    }

    public function sortMenu(){
        uasort($GLOBALS['MENU'], function ($item1, $item2) {
            return $item1['position'] <=> $item2['position'];
        });
    }

    public function getWebShopSettings(){
        $settings = $this->owner->mem->get(CACHE_SETTINGS . $this->owner->shopId);
        if(!$settings) {
            $settings = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'webshop_settings',
                    [
                        'ws_settings'
                    ],
                    [
                        'ws_shop_id' => $this->owner->shopId,
                    ]
                )
            );
            if ($settings) {
                $settings = json_decode($settings['ws_settings'], true);
                $this->owner->mem->set(CACHE_SETTINGS . $this->owner->shopId, $settings);
            }
        }

        return $settings;
    }

    public function getSliders(){
        $sliders = $this->owner->mem->get(CACHE_SLIDERS . $this->owner->shopId);
        if(!$sliders) {
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'sliders',
                    [
                        's_id AS id',
                        's_title AS title',
                        's_text AS text',
                        's_link AS link',
                        's_image AS image',
                        's_title_size AS titleSize',
                        's_text_size AS textSize',
                        's_hide_title AS hideTitle',
                    ],
                    [
                        's_shop_id' => $this->owner->shopId,
                        's_visible' => 1
                    ],
                    [],
                    false,
                    's_order'
                )
            );
            if($result){
                $sliders = [];

                foreach($result AS $row){
                    $sliders[$row['id']] = $row;
                    $sliders[$row['id']]['image'] = FOLDER_UPLOAD . $this->owner->shopId . '/sliders/' . $row['image'];
                }

                $this->owner->mem->set(CACHE_SLIDERS . $this->owner->shopId, $sliders);
            }
        }

        return $sliders;
    }

    public function getGallery(){
        $gallery = $this->owner->mem->get(CACHE_GALLERY . $this->owner->shopId);
        if(!$gallery) {
            $gallery = [];

            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'gallery',
                    [
                        'g_id AS id',
                        'g_title AS title',
                        'g_file AS image',
                    ],
                    [
                        'g_shop_id' => $this->owner->shopId,
                        'g_main' => 1
                    ],
                    [],
                    false,
                    'g_index',
                    8
                )
            );
            if($result){
                foreach($result AS $row){
                    $gallery[$row['id']] = $row;
                    $gallery[$row['id']]['thumbnail'] = str_replace('.', '_thumbnail.', $row['image']);
                }
            }

            $this->owner->mem->set(CACHE_GALLERY . $this->owner->shopId, $gallery);
        }

        return $gallery;
    }

    /*
    public function getHighlightedItems(){
        //$items = $this->owner->mem->get(CACHE_HIGHLIGHTS . $this->owner->shopId);
        $items = [];
        return $items;
    }

    public function getPopularItems(){
        //$items = $this->owner->mem->get(CACHE_POPULARS . $this->owner->shopId);
        $items = [];
        return $items;
    }

    public function getTaggedItems($tag){
        //$items = $this->owner->mem->get(CACHE_TAGGED . $this->owner->shopId . $tag);
        $items = [];
        return $items;
    }
    */
}
