<?php
include 'cron.includes.php';

$framework = new router(DEFAULT_HOST);
$framework->init();

/**
 * @var $paymentHandler PaymentHandler
 */
$paymentHandler = $framework->addByClassName('PaymentHandler');
$paymentHandler->checkPendingTransactions();