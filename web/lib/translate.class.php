<?php
class translate extends ancestor {
	const USE_CACHE = true;

	public $labels = [];

	/**
	 * @var $mem mp_memcached
	 */
	private $mem;
	private $memcache_key = 'labels-';
	private $context = 'app';
	private $newlabels = [];
	private $companyId = 0;

	public function __destruct(){
		if(!Empty($this->newlabels)){
			$this->loadLabels(true);
		}
	}

	public function init($context, $forceReload = false){
		/*
		if (class_exists('Memcache')) {
			$this->mem = new mp_memcache(MEMCACHE_HOST, MEMCACHE_PORT, MEMCACHE_COMPRESS);
		} else if (class_exists('Memcached')) {
			$this->mem = new mp_memcached(MEMCACHE_HOST, MEMCACHE_PORT);
		}
		*/

		$this->mem = $this->owner->mem;
		$this->setContext($context);

		$this->memcache_key = APPLICATION_NAME . '-labels-';
		//$this->companyId = $this->owner->shopId;
		$this->companyId = 0;

		if(!$forceReload AND self::USE_CACHE) {
			$this->labels[$this->owner->language] = $this->mem->get($this->getMemcacheKey());
		}

		if(!$this->labels[$this->owner->language] OR $forceReload){
			$this->loadLabels();
		}
	}

	public function getMemcacheKey($shopId = false, $language = false) {
		if(!is_numeric($shopId)) $shopId = $this->companyId;
		if(!$language) $language = $this->owner->language;
		return $this->memcache_key . $shopId . '-' . $this->context . '-' . $language;
	}

	public function setContext($context){
		$this->context = $context;
	}

	public function _(...$args){
		return $this->getTranslation(...$args);
	}

	public function getTranslation($label){
		if(!$this->isLabel($label)){
			return $label;
		}

		$args = func_get_args();
		unset($args[0]);

		$label = strtoupper($label);

		if(isset($this->labels[$this->owner->language][$label])){
			$string = $this->labels[$this->owner->language][$label];
		}else{
			// check db for label
			$string = $this->getLabel($label, $this->context);

			if($string){
				$this->addLabelToContext($label, $this->context);
			}else{
				$this->addLabel($label, $label, $this->context);
				$string = $label;
			}
		}

		if(!Empty($args)) {
			$cr = new customReplace;
			$cr->args = $args;

			$string = preg_replace_callback(
				'/%([0-9]+)/',
				[&$cr, 'replace'],
				$string
			);
		}

		return $string;
	}

	public function getTranslationTo($label, $language){
		$tmp_language = $this->owner->language;
		$this->owner->language = $language;
		$args = func_get_args();
		unset($args[1]);
		$string = call_user_func_array(array($this, "getTranslation"), $args);
		$this->owner->language = $tmp_language;
		return $string;
	}

	public function getAlternateTranslation($label, $default_label = ''){
		$args = func_get_args();
		unset($args[0], $args[1]);

		$string = '';
		$label = strtoupper($label);

		if(isset($this->labels[$this->owner->language][$label])){
			$string = $this->labels[$this->owner->language][$label];
		}elseif(!Empty($default_label) AND isset($this->labels[$this->owner->language][$default_label])) {
			$string = $this->labels[$this->owner->language][$default_label];
		}

		if(!Empty($args)) {
			$cr = new customReplace;
			$cr->args = $args;

			$string = preg_replace_callback(
				'/%([0-9]+)/',
				[&$cr, 'replace'],
				$string
			);
		}

		return $string;
	}

	private function loadLabels($update_memchace = true){
		// Load default language
		$default_labels = false;

		if(DEFAULT_LANGUAGE != $this->owner->language){
			$default_labels = $this->getContextLabels($this->context, DEFAULT_LANGUAGE);
		}

		// Load selected language
		$this->labels[$this->owner->language] = $this->getContextLabels($this->context, $this->owner->language);

		if($default_labels){
			// merge selected and default languages
			foreach($default_labels AS $key => $value){
				if(!isset($this->labels[$this->owner->language][$key])){
					$this->labels[$this->owner->language][$key] = $value;
				}
			}
		}

		// Store in memcache
		if($update_memchace){
			$this->mem->set($this->getMemcacheKey(), $this->labels[$this->owner->language]);
		}
	}

	private function getContextLabels($context, $language){
		$labels = Array();

		if(!is_array($context)){
			$context = [$context];
		}

		$tmp = Array();
		foreach($context AS $values){
			if(!in_array("'".$values."'", $tmp)) array_push($tmp, "'".$values."'");
		}

		$sql  = "SELECT di_label, di_value FROM ".DB_NAME_WEB.".dictionary ";
		$sql .= "LEFT JOIN ".DB_NAME_WEB.".dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='".$language."' AND di_deleted='0' ";
		$sql .= "AND dc_context IN (".implode(',', $tmp).") ";
		//$sql .= "AND (di_companyid=0 OR di_companyid='".$this->shopId."') ";
		$sql .= "GROUP BY di_companyid, di_label ORDER BY di_companyid DESC, di_label";

		$result = $this->owner->db->getRows($sql);
		if($result){
			foreach($result AS $row){
				if(!isset($labels[$row['di_label']])){
					$key = strtoupper($row['di_label']);
					$labels[$key] = $row['di_value'];
				}

			}
		}

		return $labels;
	}

	private function getLabel($label, $context = false){
		if(!Empty($label)){
			$label = strtoupper($label);

			if(!isset($this->labels[$label])){

				$sql  = "SELECT di_value FROM ".DB_NAME_WEB.".dictionary ";
				if($context) $sql .= "LEFT JOIN ".DB_NAME_WEB.".dictionary_context ON (di_label=dc_label) ";
				$sql .= "WHERE di_label='".$label."' AND di_language='".$this->owner->language."'  AND di_deleted='0' ";
				if($context) $sql .= "AND dc_context='".$context."' ";
				//$sql .= "AND di_web=0 AND (di_companyid=0 OR di_companyid='".$this->shopId."') ";
				$sql .= " ORDER BY di_companyid DESC LIMIT 1";

				$row = $this->owner->db->getFirstRow($sql);

				$this->labels[$this->owner->language][$label] = $this->newlabels[$this->owner->language][$label] = $row['di_value'];
			}
			return $this->labels[$this->owner->language][$label];
		}else{
			return '';
		}
	}

	public function addLabel($label, $value, $context){
		if(!Empty($label)){
			$shopId = 0;

			$label = strtoupper($label);
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLInsert(
					DB_NAME_WEB.".dictionary",
					[
						'di_label'    => $label,
						'di_value'    => $value,
						'di_language' => $this->owner->language,
						'di_companyid'   => $shopId,
						'di_changed'  => date("Y-m-d H:i:s"),
						'di_new'      => 1,
						'di_path'     => $_REQUEST['path']
					],
					['di_label', 'di_companyid', 'di_language']
				)
			);

			$this->addLabelToContext($label, $context);

			$this->labels[$this->owner->language][$label] = $this->newlabels[$this->owner->language][$label] = $value;
		}
	}

	public function countLabels($langfrom, $langto, $context){
		$out = [
			'total' => 0,
			'orig' => [
				'translated' => 0,
				'status' => 0,
			],
			'custom' => [
				'translated' => 0,
				'status' => 0,
			],
		];

		$items = [];

		$sql  = "SELECT di_label FROM " . DB_NAME_WEB . ".dictionary ";
		$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='" . $langfrom . "' AND di_deleted='0' ";
		$sql .= "AND di_companyid=0 ";
		$sql .= "AND dc_context = '" . $context . "' ";
		$sql .= "GROUP BY di_label";

		$result = $this->owner->db->getRows($sql);
		if($result){
			foreach($result AS $row){
				$key = strtoupper($row['di_label']);
				$items[$key][$langfrom] = 1;
				$out['total']++;
			}

			// Get corresponding translation
			$sql  = "SELECT di_companyid, di_label, di_value FROM " . DB_NAME_WEB . ".dictionary ";
			$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (di_label=dc_label) ";
			$sql .= "WHERE di_language='" . $langto . "' AND di_deleted='0' ";
			$sql .= "AND (di_companyid=0 OR di_companyid='" . $this->companyId . "') ";
			$sql .= "AND dc_context = '" . $context . "' ";
			$sql .= "GROUP BY di_companyid, di_label ";
			$sql .= "ORDER BY di_companyid DESC, di_label";

			$result = $this->owner->db->getRows($sql);

			if($result) {
				foreach ($result AS $row) {
					$key = strtoupper($row['di_label']);

					if(isset($items[$key][$langfrom])) {

						if ($row['di_companyid'] == 0) {
							if($row['di_value'] != $key AND !Empty($row['di_value'])) $out['orig']['translated']++;
						} else {
							if($row['di_value'] != $key AND !Empty($row['di_value'])) $out['custom']['translated']++;
						}
					}
				}
			}

			if($out['total']>0) {
				$out['orig']['status'] = round(($out['orig']['translated'] / $out['total']) * 100);
				$out['custom']['status'] = round(($out['custom']['translated'] / $out['total']) * 100);
			}
		}

		return $out;
	}

	public function getAllLabels($langfrom, $langto, $context, $page = 1, $load_from_first = false, $filters = [], $sort = 'key', $labels_per_page = 20) {
		$out = [
			'items' => [],
			'context' => $context,
			'stats' => $this->countLabels($langfrom, $langto, $context)
		];

		$where = [];
		$keys = [];
		if($filters['flag'] == 'not-translated' AND $langfrom != $langto){

			$sql  = "SELECT d1.di_label, d1.di_value, d2.di_label AS label, d2.di_value AS value, d2.di_language AS lang FROM " . DB_NAME_WEB . ".dictionary AS d1 ";

			$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary AS d2 ON (d1.di_label=d2.di_label AND d2.di_language='".$langto."') ";
			$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (d1.di_label=dc_label) ";

			$sql .= "WHERE d1.di_language='" . $langfrom . "' AND d1.di_deleted='0' ";
			$sql .= "AND (d1.di_companyid=0 OR d1.di_companyid='" . $this->companyId . "') ";
			$sql .= "AND dc_context = '" . $context . "' ";

			if(!Empty($filters['query'])){
				$sql .= "AND ((d1.di_value LIKE '%" . $filters['query'] . "%' OR d1.di_label LIKE '%" . $filters['query'] . "%') ";
				$sql .= "OR (d2.di_value LIKE '%" . $filters['query'] . "%' OR d2.di_label LIKE '%" . $filters['query'] . "%')) ";
			}
			$sql .= "GROUP BY d1.di_companyid, d1.di_label";

			$result = $this->owner->db->getRows($sql);
			if($result) {
				foreach($result AS $row) {
					$add = true;
					$key = strtoupper($row['label']);

					if($filters['flag'] == 'not-translated'){
						$add = false;
						if ($row['value'] == $key OR Empty($row['value']) OR Empty($row['lang'])) {
							$add = true;
						}
					}

					if(!in_array($row['di_label'], $keys) AND $add) array_push($keys, $row['di_label']);
				}

				if(!Empty($keys)) {
					foreach ($keys AS $k => $val) {
						$keys[$k] = "'" . $val . "'";
					}
				}
			}
		}

		if(!Empty($filters['query'])){
			$where[] = "(di_value LIKE '%" . $filters['query'] . "%' OR di_label LIKE '%" . $filters['query'] . "%') ";
		}
		if($filters['flag'] == 'not-translated') {
			$tmp = "(di_value = di_label OR di_value IS NULL OR di_value='')";
			if(!Empty($keys)) $tmp = "(" . $tmp . " OR di_label IN (" . implode(',', $keys) . "))";
			$where[] = $tmp;

		}elseif($filters['flag'] == 'new'){
			$where[] = "di_new='1'";
		}

		$sql  = "SELECT COUNT(di_label) AS cnt FROM " . DB_NAME_WEB . ".dictionary ";
		$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='" . $langfrom . "' AND di_deleted='0' ";
		$sql .= "AND di_companyid=0 ";
		$sql .= "AND dc_context = '" . $context . "' ";
		if($where) $sql .= "AND " . implode(" AND ", $where);

		$total = (int) $this->owner->db->getFirstRow($sql)['cnt'];
		$totalpages = ceil($total / $labels_per_page);
		$out['stats']['totalpages'] = $totalpages;

		if($page>$totalpages) $page = $totalpages;
		if($page<1) $page = 1;

		if($load_from_first) {
			$start = 0;
			$labels_per_page = $page * $labels_per_page;
		}else {
			$start = ($page * $labels_per_page) - $labels_per_page;
		}

		$sql  = "SELECT di_companyid, di_label, di_value, di_changed, di_new FROM " . DB_NAME_WEB . ".dictionary ";
		$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_language='" . $langfrom . "' ";
		$sql .= "AND (di_companyid=0 OR di_companyid='" . $this->companyId . "') ";

		$sql .= "AND dc_context = '" . $context . "' AND di_deleted='0'";
		if($where) $sql .= " AND " . implode(" AND ", $where);
		$sql .= " GROUP BY di_companyid, di_label";
		$sql .= " ORDER BY di_companyid DESC, ";

		if($sort == 'label') {
			$sql .= "di_value";
		}else{
			$sql .= "di_label";
		}

		$sql .= " LIMIT " . $start . ", " . $labels_per_page;

		$result = $this->owner->db->getRows($sql);

		if($result){
			$keys = [];

			foreach($result AS $row){
				$key = strtoupper($row['di_label']);

				if(!in_array($row['di_label'], $keys)) array_push($keys, $row['di_label']);

				if($row['di_companyid'] == 0){

					$out['items'][$key][$langfrom]['original']['value'] = $row['di_value'];
					//$out[$key][$langfrom]['original']['date'] = $row['di_changed'];

					$out['items'][$key][$langfrom]['original']['new'] = $row['di_new'];
				}else{
					if(isset($out['items'][$key][$langfrom]['original'])) {
						$out['items'][$key][$langfrom]['custom']['value'] = $row['di_value'];
						//$out[$key][$langfrom]['custom']['date'] = $row['di_changed'];

						if ($row['di_value'] == $key OR Empty($row['di_value'])) {
							$out['items'][$key][$langfrom]['original']['new'] = $row['di_new'];
						}
					}
				}
			}

			foreach($keys AS $k => $val){
				$keys[$k] = "'" . $val . "'";
			}

			// Get corresponding translations
			$sql = "SELECT di_companyid, di_label, di_value, di_changed FROM " . DB_NAME_WEB . ".dictionary ";
			//$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (di_label=dc_label) ";
			$sql .= "WHERE (di_companyid=0 OR di_companyid='" . $this->companyId . "') AND di_language='" . $langto . "' ";
			//$sql .= "AND dc_context = '" . $context . "' ";
			$sql .= "AND di_label IN (" . implode(',', $keys) . ") AND di_deleted='0' ";
			$sql .= "GROUP BY di_companyid, di_label ";
			//$sql .= "ORDER BY di_companyid DESC, di_label";

			$result = $this->owner->db->getRows($sql);
			if ($result) {
				foreach ($result AS $row) {
					$key = strtoupper($row['di_label']);

					if (isset($out['items'][$key][$langfrom])) {

						if ($row['di_companyid'] == 0) {
							$out['items'][$key][$langto]['original']['value'] = $row['di_value'];
							$out['items'][$key][$langto]['original']['date'] = $row['di_changed'];
						} else {
							$out['items'][$key][$langto]['custom']['value'] = $row['di_value'];
							$out['items'][$key][$langto]['custom']['date'] = $row['di_changed'];
						}
					}
				}
			}
		}

		return $out;
	}

	public function saveTranslation($langto, $label, $value, $context){
		$out = false;

		$this->setContext($context);

		$shopId = $this->companyId;
		$value = trim($value);

		if(Empty($value) AND $shopId){
			$this->markLabelForDelete($label, $langto, $shopId);
		}else {
			$sql = $this->owner->db->genSQLInsert(
				DB_NAME_WEB . ".dictionary",
				[
					'di_label' => $label,
					'di_value' => $value,
					'di_language' => strtolower($langto),
					'di_changed' => 'NOW()',
					'di_new' => 2,
					'di_deleted' => 0,
					'di_companyid' => $shopId
				],
				[
					'di_label',
					'di_language',
					'di_companyid',
				]
			);
			$this->owner->db->sqlQuery($sql);
		}

		// update memcache key value
		$this->mem->delete($this->getMemcacheKey(0, $langto));

		return $out;
	}

	public function deleteLabels($labels = []){
		if(Empty($labels)) {
			$sql = "DELETE FROM " . DB_NAME_WEB . ".dictionary WHERE di_deleted='1'";
			$this->owner->db->sqlQuery($sql);
		}else{
			foreach($labels AS $label){
				$sql = "DELETE FROM " . DB_NAME_WEB . ".dictionary WHERE di_label='" . $label . "'";
				$this->owner->db->sqlQuery($sql);
			}
		}
	}

	public function markLabelForDelete($label, $lang = false, $shopId = false){
		$sql = "UPDATE " . DB_NAME_WEB . ".dictionary SET di_deleted='1' WHERE di_label='" . $label . "'";
		if($shopId AND $lang){
			$sql .= " AND di_language='" . $lang . "' AND di_companyid='" . $shopId . "' LIMIT 1";
		}
		$this->owner->db->sqlQuery($sql);
	}

	private function addLabelToContext($label, $context){
		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				DB_NAME_WEB.".dictionary_context",
				[
					'dc_context' => $context,
					'dc_label'   => strtoupper($label)
				],
				['dc_context']
			)
		);
	}

	/*
	 * Sync functions
	 */
	public function loadLabelSet($language, $shopId = 0){
		$lang = [];
		$labels = [];

		if(!is_array($language)){
			array_push($lang, $language);
		}else{
			foreach($language AS $l){
				array_push($lang, "'" . $l . "'");
			}
		}

		$sql  = "SELECT di_label, di_value, di_language, dc_context FROM " . DB_NAME_WEB . ".dictionary ";
		$sql .= "LEFT JOIN " . DB_NAME_WEB . ".dictionary_context ON (di_label=dc_label) ";
		$sql .= "WHERE di_companyid='" . $shopId . "' AND di_language IN (" . implode(',', $lang) . ") AND di_new='2' AND di_deleted='0'";

		$result = $this->owner->db->getRows($sql);
		if($result){
			foreach($result AS $row){
				$lang = $row['di_language'];
				if(!isset($labels[$row['di_label']])){
					$key = strtoupper($row['di_label']);
					$labels[$lang][$key]['value'] = $row['di_value'];
					$labels[$lang][$key]['context'] = $row['dc_context'];
				}

			}
		}

		return $labels;
	}

	public function markLabelsSynced($labels, $shopId = 0){
		$out = false;

		if($labels){
			foreach($labels AS $lang => $data){
				foreach($data AS $key => $value){
					$sql = $this->owner->db->genSQLUpdate(
						DB_NAME_WEB . '.dictionary',
						[
							'di_new' => 0
						],
						[
							'di_label' => $key,
							'di_language' => $lang,
							'di_companyid' => $shopId
						]
					);

					$this->owner->db->sqlQuery($sql);
					$out .= $sql . "\n";
				}
			}

		}

		return $out;
	}

	public function updateLabelSet($labels, $shopId = 0){
		$out = false;

		if($labels){
			foreach($labels AS $lang => $data){
				foreach($data AS $key => $value){
					$sql = $this->owner->db->genSQLInsert(
						DB_NAME_WEB . '.dictionary',
						[
							'di_label' => $key,
							'di_value' => $value['value'],
							'di_language' => $lang,
							'di_companyid' => $shopId,
							'di_changed' => 'NOW()',
							'di_new' => 0
						],
						[
							'di_label',
							'di_language',
							'di_companyid'
						]
					);

					$this->owner->db->sqlQuery($sql);

					$this->addLabelToContext($key, $value['context']);
				}
			}

			$out = true;
		}

		return $out;
	}

	public function listDeletedLabels($shopId = 0){
		$out = [];

		$sql = "SELECT di_label FROM " . DB_NAME_WEB . ".dictionary WHERE di_deleted = 1";
		if($shopId){
			$sql .= " AND di_companyid='" . $shopId . "'";
		}
		$sql .= " GROUP BY di_label";

		$result = $this->owner->db->getRows($sql);

		if($result) {
			foreach ($result AS $row) {
				$out[] = $row['di_label'];
			}
		}

		return $out;
	}

	public function deleteUnusedLabels(){
		$sql = "DELETE FROM " . DB_NAME_WEB . ".dictionary WHERE di_deleted = 1";
		$this->owner->db->sqlQuery($sql);
	}

	public function removeUnusedContextItems(){
		$sql = "SELECT dc_context, dc_label FROM " . DB_NAME_WEB . ".dictionary_context LEFT JOIN " . DB_NAME_WEB . ".dictionary ON dc_label = di_label WHERE di_label IS NULL";
		$res = $this->owner->db->getRows($sql);
		if($res){
			foreach($res AS $row){
				$sql = "DELETE FROM " . DB_NAME_WEB . ".dictionary_context WHERE dc_context = '".$row['dc_context']."' AND dc_label = '".$row['dc_label']."' LIMIT 1";
				$this->owner->db->sqlQuery($sql);
			}
		}
	}

	public function clearTranslationCache($shopId = 0){
		$shops = [];
		array_push($shops, $shopId);

		$sql = "SELECT DISTINCT(dc_context) AS context FROM " . DB_NAME_WEB . ".dictionary_context";
		$res = $this->owner->db->getRows($sql);
		if($res){
			foreach($res AS $row){
				foreach($GLOBALS['HOSTS'][$this->owner->host] AS $lang => $name){
					foreach($shops AS $shopId) {
						$key = $this->memcache_key . $shopId . '-' . $row['context'] . '-' . $lang;
						$this->owner->mem->delete($key);
					}
				}
			}
		}
	}

	private function isLabel($label){
		$check = [
			'LBL_',
			'BTN_',
			'ERR_',
			'TXT_',
			'MSG_',
			'MENU_',
			'CONFIRM_',
		];

		foreach ($check as $string){
			$pos = stripos($label, $string);
			if ($pos !== false){
				return true;
			}
		}

		return false;
	}

}

class customReplace {
	public $args;

	function replace($matches){
		return $this->args[$matches[1]];
	}
}