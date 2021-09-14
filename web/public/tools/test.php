<?php
require_once(__DIR__ . '/../web.includes.php');

$router = new router();
$router->init();


/**
 * @var $settlement settlement
 */
$settlement = $router->addByClassName('settlement');
$settlement->init(2);
$settlement->createSettlement('2021-07-01', '2021-07-30');




exit();

/**
 * @var $notification ntEvent
 */
$notification = $router->addByClassName('ntAlertMileage', false, [
    14,
    1
]);



$router->notifications->sendNotifications($notification);
