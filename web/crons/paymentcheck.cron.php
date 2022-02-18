<?php
include 'cron.includes.php';

$framework = new router();
$framework->init();

/**
 * @var $paymentHandler PaymentHandler
 */
$paymentHandler = $framework->addByClassName('PaymentHandler');
$paymentHandler->checkPendingTransactions();