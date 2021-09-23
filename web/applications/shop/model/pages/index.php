<?php
/**
 * @var $this router
 */

$this->data['sliders'] = $this->lib->getSliders();
$this->data['highlights'] = $this->lib->getHighlightedItems();
$this->data['populars'] = $this->lib->getPopularItems();

$this->data['sections'] = $this->lib->getWidgetContents('home');

//dd($this->data['sliders']);