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
    'smscallback' => [
        'display' => 0
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
    'my-profile' => [
        'display' => 0,
        'access'  => true,
    ],

	'webshop' => [
		'display' => 2,
		'icon'  => 'fa-store',
        'userGroups' => [USER_GROUP_ADMINISTRATORS],
        'items'    => [
            'products' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'categories' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'paymodes' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'shippingmodes' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'properties' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'documents' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'packaging' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'units' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
            'settings' => [
                'display' => 1,
                'access'  => true,
                'userGroups' => [USER_GROUP_ADMINISTRATORS]
            ],
        ]
	],

    'customers' => [
        'display' => 1,
        'icon'  => 'fa-users',
        'access'  => true,
    ],

    'orders' => [
        'display' => 1,
        'icon'  => 'fa-shopping-cart',
        'access'  => true,
    ],

    'settings' => [
		'display'  => 2,
		'icon'  => 'fa-cog',
		'userGroups' => [USER_GROUP_ADMINISTRATORS],
		'items'    => [
			'system' => [
				'display' => 2,
				'items'   => [
                    'hosts' => [
                        'display' => 1,
                        'access'  => true,
                        'userGroups' => [USER_GROUP_ADMINISTRATORS]
                    ],
					'useraccesslevel' => [
						'display' => 1,
						'access'  => true,
						'userGroups' => [USER_GROUP_ADMINISTRATORS]
					],
                    'administrators' => [
                        'display' => 1,
                        'access'  => true,
                        'userGroups' => [USER_GROUP_ADMINISTRATORS]
                    ]
				]
			],
			'lists' => [
				'display' => 2,
				'items'   => [
				],
			],
			'content' => [
				'display' => 2,
				'items'   => [
					'dictionary' => [
						'display' => 1,
						'access'  => true,
						'userGroups' => [USER_GROUP_ADMINISTRATORS]
					],
					'templates' => [
						'display' => 1,
						'access'  => true,
						'userGroups' => [USER_GROUP_ADMINISTRATORS]
					],
                    'pages' => [
                        'display' => 1,
                        'access'  => true,
                        'userGroups' => [USER_GROUP_ADMINISTRATORS]
                    ],
                    'sliders' => [
                        'display' => 1,
                        'access'  => true,
                        'userGroups' => [USER_GROUP_ADMINISTRATORS]
                    ],
                    'gallery' => [
                        'display' => 1,
                        'access'  => true,
                        'userGroups' => [USER_GROUP_ADMINISTRATORS]
                    ],
				]
			],
		]
	],
];