<?php
const WEB_ROOT = __DIR__ . '/';
include_once __DIR__ . '/../../includes.php';

autoloader::setFileExt('.class.php');
autoloader::setPath(DIR_WEB . 'lib');
spl_autoload_register('autoloader::loader');
