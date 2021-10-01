<?php
/**
 * @var $this router
 */

if($this->settings['stopSale']){
    $this->pageRedirect('/');
}

$this->cart->init();
$this->data['sections'] = $this->lib->getWidgetContents('cart');
