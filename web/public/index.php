<?php
require_once(__DIR__ . '/web.includes.php');

if(DEBUG_ON) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}

if(SET_AUTH) {
	$authRealm = 'Restricted area';

	if (empty($_SESSION['authenticated_supervisor']) && isset($_SERVER['PHP_AUTH_USER'])) {
		if ($_SERVER['PHP_AUTH_PW'] == AUTH_PWD && $_SERVER['PHP_AUTH_USER'] == AUTH_USER) {
			$_SESSION['authenticated_supervisor'] = true;
		}
	}

	if (empty($_SESSION['authenticated_supervisor'])) {
		header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
		header('HTTP/1.0 401 Unauthorized');
		die('You are not authorized to see this page.');
	}
}

$framework = new router();
$framework->init();
$framework->display();
