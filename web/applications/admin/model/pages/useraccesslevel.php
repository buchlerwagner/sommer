<?php
/**
 * @var $this router
 */

$this->data['addFunction'] = true;
$this->data['viewFunction'] = true;

$this->data['group'] = $this->getSession('selected_group');

if(isset($_REQUEST['group'])) {
	$this->data['group'] = $_REQUEST['group'];
}
if(empty($this->data['group'])) {
	$this->data['group'] = USER_GROUP_ADMINISTRATORS;
}
$this->setSession('selected_group', $this->data['group']);

$menu = [];
if($GLOBALS['USER_GROUPS'][$this->data['group']]['app']){
    include(DOC_ROOT . 'web/applications/' . $GLOBALS['USER_GROUPS'][$this->data['group']]['app'] . '/menu.php');
    $menu = $GLOBALS['MENU'];
}

$this->data['pages'] = $this->getAllAccessMenus($menu, ['index'], $this->data['group']);
$this->data['userGroups'] = $this->lib->getList('userGroups');
//dd($this->data['pages']);

$this->data['roles'] = $this->lib->getList('roles', ['group' => $this->data['group']]);

$this->data['rights'] = [];
$sql = "SELECT * FROM " . DB_NAME_WEB . ".access_levels
			WHERE al_group = '" . $this->data['group'] . "'
				ORDER BY al_page";

$res = $this->db->getRows( $sql	);
foreach($res as $row) {
	$this->data['rights'][$row['al_role']][$row['al_page']]['value'] = (int) $row['al_right'];
}

$this->data['function_rights'] = [];
if($this->data['viewFunction']) {
	$this->data['functions'] = $this->lib->getList('access_functions');

	$sql = "SELECT * FROM " . DB_NAME_WEB . ".access_function_rights
				WHERE afr_group='" . $this->data['group'] . "'
				ORDER BY afr_page";

	$res = $this->db->getRows($sql);
	foreach ($res as $row) {
		$this->data['function_rights'][$row['afr_role']][$row['afr_key']] = 1;
	}
}

$this->data['accessoptions'] = [
	ACCESS_RIGHT_NO => [
		'icon'  => 'times',
		'color' => 'danger',
		'label' => 'LBL_NO_ACCESS',
	],
	ACCESS_RIGHT_READONLY => [
		'icon'  => 'eye',
		'color' => 'primary',
		'label' => 'LBL_READ_ONLY',
	],
	ACCESS_RIGHT_WRITE => [
		'icon'  => 'check',
		'color' => 'success',
		'label' => 'LBL_FULL_ACCESS',
	]
];
