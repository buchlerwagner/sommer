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
$this->addJs('feather-icons/feather.min.js', 'jquery', true, false, false);
$this->addJs('jquery/jquery-3.4.1.min.js', 'jquery', false, false, false);
$this->addJs('jquery-ui/jquery-ui.min.js', 'jquery-ui', false, false, false);

$this->addCss('bootstrap-select/css/bootstrap-select.css', 'bs-select', false, false);
$this->addJs('bootstrap/bootstrap.bundle.min.js', 'bootstrap', false, false, false);
$this->addJs("autocomplete/bootstrap-autocomplete.min.js", 'autocomplete', false, false, false);
$this->addJs("zoom/jquery.zoom.min.js", 'zoom', false, false, false);

//$this->addJs('select2/js/select2.min.js', 'bs-select2', false, false, false);
//$this->addCss('select2/css/select2.css', 'bs-select2', false, false);

$this->addCss('flatpickr/flatpickr.min.css', 'flatpickr', false, false);
$this->addJs('flatpickr/flatpickr.min.js', 'flatpickr', false, false, false);
$this->addJs('flatpickr/l10n/' . $this->owner->language . '.js', 'flatpickr_lng', false, false, false);

$this->addJs('bootstrap-select/js/bootstrap-select.min.js', 'bs-select', false, false, false);
if($this->owner->language != 'en'){
	$this->addJs('bootstrap-select/js/i18n/defaults-' . strtolower($this->owner->language) . '_' . strtoupper($this->owner->language) . '.js', 'bs-select-i18n', false, false, false);
}

$this->addJs('admin' . $postfix . '.js', 'app-' . APPLICATION_NAME, false, false, $addVersion);


/**
 * @todo remove
 */
$this->addJs('order.js', 'order', false, false, false);
