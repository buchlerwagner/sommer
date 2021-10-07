<?php
/**
 * @var $this router
 * @var $content content
 */

$this->loadForm('contact');
$this->data['sections'] = $this->lib->getWidgetContents('contact');
$this->view->includeValidationJS();

$this->data['isSuccess'] = (isset($_REQUEST['success']));
