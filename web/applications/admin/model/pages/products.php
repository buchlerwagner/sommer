<?php
/**
 * @var $this router
 */

$this->data['filterForm'] = $this->loadForm('productsFilters');
$this->data['table'] = $this->loadTable('products');
$this->page = 'page-table';
