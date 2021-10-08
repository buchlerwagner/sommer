<?php
/**
 * @var $this router
 */

if (!$this->user->isLoggedIn()) {
    $this->pageRedirect('/');
}

$this->loadForm('profile');
$this->data['isSuccess'] = (isset($_REQUEST['success']));

