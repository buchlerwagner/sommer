<?php
/**
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
		'display'  => 0,
		'access'   => true,
	],
    'ajax' => [
		'display' => 0,
	],
	'login' => [
		'layout'  => 'simple',
		'display' => 0
	],
	'logout' => [
		'display' => 0,
	],
	'set-new-password' => [
		'layout'  => 'simple',
		'display' => 0,
	],
    'pprofilom' => [
        'display' => 0,
        'access'  => true,
    ],
];