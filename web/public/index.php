<?php
require_once(__DIR__ . '/web.includes.php');

if(isApiRequest()){
    include_once __DIR__ . '/../../config/api-constants.php';
    require_once  __DIR__ . '/../lib/models/api/apiException.php';

    $api = new api();
    $api->start();
}else{
    if(DEBUG_ON) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
    }

    $framework = new router();
    $framework->init();
    $framework->display();
}