<?php
include_once WEB_ROOT . '../lib/fileuploader.class.php';

/**
 * @var $this router
 */

$this->output = OUTPUT_RAW;

$this->data = $this->view->renderContent('cms-gallery', []);
