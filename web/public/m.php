<?php
if($_GET["cmid"] && $_GET["ctid"]){
	require_once(__DIR__ . '/web.includes.php');
	$cmid = (int) $_GET['cmid'];
	$ctid = (int) $_GET['ctid'];

	$db = db::factory(DB_TYPE, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_WEB, DB_ENCODING);
	$db->sqlQuery(
		$db->genSQLUpdate(
			DB_NAME_WEB . ".crm_mails",
			[
				'cm_viewed' => 'INCREMENT',
				'cm_view_date' => 'NOW()'
			],
			[
				'cm_id' => $cmid,
				'cm_ct_id' => $ctid,
				'cm_folder' => 'SENT',
			]
		)
	);

	$db->sqlQuery(
		$db->genSQLUpdate(
			DB_NAME_WEB . ".crm_threads",
			[
				'ct_viewed' => 1,
			],
			[
				'ct_id' => $ctid
			]
		)
	);


	header('Content-type: image/gif');
	print file_get_contents(__DIR__ . '/images/blank.gif');
	exit();
}
