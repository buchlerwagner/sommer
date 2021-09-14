<?php
/**
 * @var $this router
 */

if ($this->user->isLoggedIn()) {
	$this->pageRedirect('/');
}

$this->loadForm('newPassword');
$this->data['success'] = (isset($_REQUEST['success']) ? true : false);