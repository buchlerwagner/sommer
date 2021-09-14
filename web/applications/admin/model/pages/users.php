<?php
/**
 * @var $this router
 */

if($_REQUEST['action'] == 'getDoc'){
    /**
     * @var $doc docTransferProtocol
     */
    $doc = $this->addByClassName('docTransferProtocol');
    $doc->setUserId($_REQUEST['usid']);
    $doc->setAssignId($_REQUEST['ufid']);

    $doc->getPDF();
}

$this->data['filterForm'] = $this->loadForm('userFilters');
$this->data['table'] = $this->loadTable('users');

$this->page = 'page-table';
