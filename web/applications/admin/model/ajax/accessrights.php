<?php
/**
 * @var $this router
 */

if (!empty($_REQUEST['usergroup']) && !empty($_REQUEST['page']) && isset($_REQUEST['role']) && $this->user->hasPageAccess('useraccesslevel', ACCESS_RIGHT_WRITE)) {
	$role = $this->db->escapestring($_REQUEST['role']);
	$userGroup = $this->db->escapestring($_REQUEST['usergroup']);
	$right = (int)$this->db->escapestring($_REQUEST['value']);
	$page = $this->db->escapestring($_REQUEST['page']);
	$function = $this->db->escapestring($_REQUEST['function']);

	if (!$function) {
		if (empty($right)) {
			$this->db->sqlQuery(
				"DELETE FROM " . DB_NAME_WEB . ".access_levels WHERE al_group = '" . $userGroup . "' AND al_page = '" . $page . "' AND al_role = '" . $role . "'"
			);
		} else {
			$this->db->sqlQuery(
				$this->db->genSQLInsert(
					DB_NAME_WEB . ".access_levels",
					[
						'al_role' => $role,
						'al_group' => $userGroup,
						'al_page' => $page,
						'al_right' => $right,
					],
					[
						'al_role',
						'al_group',
						'al_page',
					]
				)
			);
		}
	} else {
		if (empty($_REQUEST['checked'])) {
			$this->db->sqlQuery(
				"DELETE FROM " . DB_NAME_WEB . ".access_function_rights WHERE afr_group = '" . $userGroup . "' AND afr_page = '" . $page . "' AND afr_role = '" . $role . "'"
			);
		} else {
			$this->db->sqlQuery(
				$this->db->genSQLInsert(
					DB_NAME_WEB . ".access_function_rights",
					[
						'afr_role' => $role,
						'afr_group' => $userGroup,
						'afr_page' => $page,
						'afr_key' => $function,
					]
				)
			);
		}
	}
}

exit();
