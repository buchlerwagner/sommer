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

$this->addCss('roboto/roboto.min.css', 'roboto', false, false);
$this->addCss('jquery-ui/jquery-ui.min.css', 'jquery-ui', false, false);
$this->addCss('fontawesome-pro-5/css/all.min.css', 'fontawesome', false, false);

$this->addCss($this->owner->theme . '.style' . $postfix . '.css', 'app-' . APPLICATION_NAME, false, $addVersion);
$this->addJs('jquery/jquery-3.4.1.min.js', 'jquery', false, false, false);
$this->addJs('jquery-ui/jquery-ui.min.js', 'jquery-ui', false, false, false);

//$this->addCss('bootstrap-select/css/bootstrap-select.css', 'bs-select', false, false);
$this->addJs('bootstrap/bootstrap.bundle.min.js', 'bootstrap', false, false, false);
//$this->addJs("autocomplete/bootstrap-autocomplete.min.js", 'autocomplete', false, false, false);
//$this->addJs("zoom/jquery.zoom.min.js", 'zoom', false, false, false);

$this->addJs('shop' . $postfix . '.js', 'app-' . APPLICATION_NAME, false, false, $addVersion);
