<?php
/**
 * @var $this router
 */

$this->data['filterForm'] = $this->loadForm('orderFilters');
$this->data['table'] = $this->loadTable('orders');

$this->page = 'page-table';
