<?php
/**
 * @var $this router
 */

if($this->settings['stopSale']){
    $this->pageRedirect('/');
}

$this->cartHandler->init();
$this->data['sections'] = $this->lib->getWidgetContents('cart');

//dd($this->cartHandler->getCartItems());