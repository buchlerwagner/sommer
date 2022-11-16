<?php
/**
 * @var $this router
 */

$key = false;
if(!Empty($this->params[0])){
    $key = $this->params[0];
}

$this->cartHandler->init($key, false);

if($this->settings['stopSale'] || $this->cartHandler->isEmpty()){
    $this->pageRedirect('/');
}elseif($this->cartHandler->getStatus() == CartHandler::CART_STATUS_ORDERED){
    $this->pageRedirect($this->getPageName('finish') . $key . '/');
}

$this->data['loginError'] = [];

$this->loadForm('login');
$this->loadForm('order');

$this->data['sections'] = $this->lib->getWidgetContents('checkout');

$this->view->addCss('flatpickr/flatpickr.min.css', 'flatpickr', false, false);
$this->view->addJs('flatpickr/l10n/' . $this->language . '.js', 'flatpickr_lng', false, false, false);
$this->view->includeValidationJS();
