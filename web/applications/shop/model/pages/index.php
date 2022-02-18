<?php
/**
 * @var $this router
 */

$this->data['sliders'] = $this->lib->getSliders();

$shop = $this->addByClassName('webShop');
$shop->init($this->shopId);

/**
 * @var $shop webShop
 */
$this->data['highlightedItems'] = $shop->getHighlightedProducts(false, [], 12);
//$this->data['popularItems'] = $shop->getPopularItems();

$this->data['sections'] = $this->lib->getWidgetContents('home');
$this->data['gallery'] = $this->lib->getGallery(1);

//dd($this->data['gallery']);