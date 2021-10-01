<?php
/**
 * @var $this router
 * @var $shop webShop
 */

$this->data['sliders'] = $this->lib->getSliders();

$shop = $this->addByClassName('webShop');
$shop->init($this->shopId);

$this->data['highlightedItems'] = $shop->getHighlightedProducts(false, [], 12);
//$this->data['popularItems'] = $shop->getPopularItems();

$this->data['sections'] = $this->lib->getWidgetContents('home');

//dd($this->data['sliders']);