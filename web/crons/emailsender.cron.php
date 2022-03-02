<?php
include 'cron.includes.php';

$ibe = new router(DEFAULT_HOST);
$ibe->init();

$res = $ibe->db->getRows(
	"SELECT em_id, em_language FROM " . DB_NAME_WEB . ".emails WHERE em_status = 0 LIMIT 0, 50"
);
if (!empty($res)) {
	foreach ($res as $row) {
		$ibe->language = $row['em_language'];
		$err = $ibe->email->send($row['em_id']);
		if (!empty($err)) {
			echo date("Y-m-d H:i:s") . ': ' . $err . "\n";
		}
	}
}
