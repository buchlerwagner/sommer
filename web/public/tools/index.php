<?php
require_once(__DIR__ . '/../web.includes.php');

$fw = new router();
$fw->init();

$config = $this->owner->getHostConfig();

$publicDomain = rtrim($config['publicSite'], '/') . '/';

d($publicDomain);
dd($config);

include_once('header.php');
?>


<?php include_once('footer.php'); ?>
