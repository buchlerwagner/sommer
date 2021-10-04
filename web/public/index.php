<?php
require_once(__DIR__ . '/web.includes.php');

if(DEBUG_ON) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}

$framework = new router();
$framework->init();
$framework->display();
