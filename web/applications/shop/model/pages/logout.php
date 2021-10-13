<?php
/**
 * @var $this router
 */

$this->cart->destroyKey();
$this->delSession();
$this->pageRedirect('/');
