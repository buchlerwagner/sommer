<?php
require_once(__DIR__ . '/../web.includes.php');

$ibe = new router();
$ibe->init();

$ibe->translate->deleteUnusedLabels();
$ibe->translate->removeUnusedContextItems();