<?php
/**
 * @var $this router
 */

$this->data['filterForm'] = $this->loadForm('customerFilters');
$this->data['table'] = $this->loadTable('customers');

$this->page = 'page-table';
