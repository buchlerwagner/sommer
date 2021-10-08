<?php
/**
 * @var $this router
 */

if ($this->user->isLoggedIn()) {
    $this->pageRedirect('/');
}

$this->data['sections'] = $this->lib->getWidgetContents('register');

$this->loadForm('register');
$this->data['isSuccess'] = (isset($_REQUEST['success']));
//$this->view->includeValidationJS();
