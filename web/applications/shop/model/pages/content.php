<?php
/**
 * @var $this router
 * @var $content content
 */

$content = $this->addByClassName('content');
$content->init($this->originalPage);
$this->data['content'] = $content->getContent();
