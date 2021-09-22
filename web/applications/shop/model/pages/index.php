<?php
/**
 * @var $this router
 */

$this->data['sliders'] = $this->lib->getSliders();
$this->data['highlights'] = $this->lib->getHighlightedItems();
$this->data['populars'] = $this->lib->getPopularItems();

//dd($this->data['sliders']);