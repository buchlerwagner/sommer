<?php
$GLOBALS['HOSTS'] = [
    HOST_ADMIN => [
		'shopId'	 	=> 1,
		'publicUrl'	 	=> 'https://sommer-cukraszda.hu/',
		'forceSSL'	 	=> false,
		'application' 	=> 'admin',
		'language'		=> 'hu',
		'timezone'		=> 29,
		'languages'   => [
			'hu' => 'Magyar',
			//'en' => 'English'
		],
		'emails' => [
		    'default' => [
                'name' 	  => 'Sommer Cukrászda',
                'address' => 'info@sommer-cukraszda.hu',
            ]
		],
		'country'     => 'hu',
		'currency'    => 'HUF',
		'currencies'  => [
			'HUF' => 'Ft',
		],
		'sitedata' => [
            'title'	=> 'sommer-cukraszda.hu Admin',
            'theme' => [
                'name'      => 'mimity',
            ],
		]
	],
    HOST_CLIENTS => [
        'shopId'	 	=> 1,
        'forceSSL'	 	=> false,
        'application' 	=> 'shop',
        'language'		=> 'hu',
        'timezone'		=> 29,
        'languages'   => [
            'hu' => 'Magyar',
            //'en' => 'English'
        ],
        'emails' => [
            'default' => [
                'name' 	  => 'Sommer Cukrászda',
                'address' => 'info@sommer-cukraszda.hu',
            ]
        ],
        'country'     => 'hu',
        'currency'    => 'HUF',
        'currencies'  => [
            'HUF' => 'Ft',
        ],
        'sitedata' => [
            'title'	=> 'Sommer Cukrászda',
            'theme' => [
                'name'      => 'bellaria',
            ],
        ]
    ],
];

if(SERVER_ID === 'development'){
    $GLOBALS['HOSTS']['admin.sommer.test'] = $GLOBALS['HOSTS'][HOST_ADMIN];
    $GLOBALS['HOSTS']['admin.sommer.test']['publicUrl'] = 'http://sommer.test/';
    $GLOBALS['HOSTS']['sommer.test'] = $GLOBALS['HOSTS'][HOST_CLIENTS];

    $GLOBALS['HOSTS']['admin.wagnr.hu'] = $GLOBALS['HOSTS'][HOST_ADMIN];
    $GLOBALS['HOSTS']['admin.wagnr.hu']['publicUrl'] = 'http://shop.wagnr.hu/';
    $GLOBALS['HOSTS']['shop.wagnr.hu'] = $GLOBALS['HOSTS'][HOST_CLIENTS];
}