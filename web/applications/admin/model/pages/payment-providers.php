<?php
/**
 * @var $this router
 */

$this->data['table'] = $this->loadTable('paymentProviders');
$this->page = 'page-table';

$this->view->addCss('bootstrap-fileinput/css/fileinput.min.css', 'fileinput', false, false);
$this->view->addJs('bootstrap-fileinput/js/fileinput.min.js', 'fileinput', false, false, false);
$this->view->addJs('bootstrap-fileinput/themes/fas/theme.min.js', 'fileinput-theme', false, false, false);
