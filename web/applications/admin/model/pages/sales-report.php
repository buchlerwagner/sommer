<?php
/**
 * @var $this router
 */

$this->data['filterForm'] = $this->loadForm('salesReportFilters');
$this->data['table'] = $this->loadTable('salesReport');
$this->page = 'page-table';
