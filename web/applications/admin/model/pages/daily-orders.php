<?php
/**
 * @var $this router
 */

$this->data['filterForm'] = $this->loadForm('dailyOrderFilters');
$this->data['table'] = $this->loadTable('dailyOrders');

if($_REQUEST['print']){
    switch($_REQUEST['print']){
        case 'product-list':
            /**
             * @var $doc PrintTodayProducts
             */

            $doc = $this->addByClassName('PrintTodayProducts');
            $doc->setTable($this->data['table']);
            $doc->getPDF();

            break;
        case 'orders':
            /**
             * @var $doc PrintOrders
             */

            $doc = $this->addByClassName('PrintOrders');
            $doc->setFilters($this->data['table']->getFilters());
            $doc->getPDF();

            break;
        case 'labels':

            /**
             * @var $doc PrintLabels
             */

            $doc = $this->addByClassName('PrintLabels');
            $doc->setFilters($this->data['table']->getFilters());
            $doc->getPDF();

            break;
    }
}

$this->page = 'page-table';
