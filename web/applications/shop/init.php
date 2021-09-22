<?php
/**
 * @var $this view
 */

$addVersion = true;
$postfix = '.min';
if(SERVER_ID == 'development') {
	$addVersion = false;
	$postfix = '';
}

//$this->addCss('jquery-ui/jquery-ui.min.css', 'jquery-ui', false, false);
$this->addCss('fontawesome-pro-5/css/all.min.css', 'fontawesome', false, false);
$this->addCss($this->owner->theme . '.style' . $postfix . '.css', 'app-' . APPLICATION_NAME, false, $addVersion);

$this->addJs('jquery/jquery-3.4.1.min.js', 'jquery', false, false, false);
//$this->addJs('jquery/jquery-1.12.4.min.js', 'jquery', false, false, false);
//$this->addJs('jquery-ui/jquery-ui.min.js', 'jquery-ui', false, false, false);
//$this->addCss('bootstrap-select/css/bootstrap-select.css', 'bs-select', false, false);
$this->addJs('bootstrap/bootstrap.bundle.min.js', 'bootstrap', false, false, false);

$this->addJs($this->owner->theme . $postfix . '.js', 'theme-' . APPLICATION_NAME, false, false, $addVersion);
$this->addJs('shop' . $postfix . '.js', 'app-' . APPLICATION_NAME, false, false, $addVersion);
