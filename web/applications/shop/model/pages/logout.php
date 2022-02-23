<?php
/**
 * @var $this router
 */

$this->cartHandler->destroyKey();
$this->delSession();
$this->pageRedirect('/');
