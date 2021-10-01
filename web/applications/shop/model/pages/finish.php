<?php
/**
 * @var $this router
 */

$key = false;
if(!Empty($this->params[0])){
    $key = $this->params[0];
}

if(!$key){
    $this->pageRedirect('/');
}else {
    $this->cart->init($key, false);

    if ($this->cart->getStatus() != cart::CART_STATUS_ORDERED || Empty($this->cart->items)) {
        $this->pageRedirect('/');
    }

    $this->data['sections'] = $this->lib->getWidgetContents('finish');
}
