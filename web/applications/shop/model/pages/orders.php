<?php
/**
 * @var $this router
 */

if (!$this->user->isLoggedIn()) {
    $this->pageRedirect('/');
}

$this->view->addJs('tables.js', 'tables');
$this->data['table'] = $this->loadTable('myOrders');

