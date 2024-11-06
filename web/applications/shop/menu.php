<?php
/**
 * @var $this router
 *
 * $menuItem = [
 *	'display'  => 0,
 *	'access'   => true,      	// if missing, access right must be calculated from submenu access rights
 *	'layout'   => 'main', 		// page layout
 *	'badge'    => '10'       	// optional: string to display in badge
 *	'icon'     => ''
 *	'items'    => []         	// array of submenu items
 * ]
 *
 * Display values:
 * 0 - invisible
 * 1 - visible
 * 2 - menu group
 * 10 - label
 *
 * Access values:
 * false    - no login needed
 * true     - bool|array login needed
 *
 */

$GLOBALS['MENU'] = [
	'index' => [
		'display' => 1,
        'header' => true,
	],
    'payment-check' => [
        'display' => 0,
    ],
    'payment-callback' => [
        'display' => 0,
    ],
    'payment-error' => [
        'display' => 0,
    ],
    'ajax' => [
		'display' => 0,
	],
	'login' => [
		'display' => 0
	],
	'logout' => [
		'display' => 0,
	],
];

// Generic pages
foreach($GLOBALS['PAGE_NAMES'][$this->language] AS $pageName => $localisedPage){
    $GLOBALS['MENU'][$pageName] = $localisedPage;
    $GLOBALS['MENU'][$pageName]['pagemodel'] = $pageName;
}

// Content pages
$this->lib->setContentPageMenus();
$this->lib->setProductCategories();
$this->lib->sortMenu();
