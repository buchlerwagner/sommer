<?php
$GLOBALS['HOSTS'] = [
    HOST_ADMIN => [
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
                'name' 	  => 'Sommer Cukraszda',
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
                'version'   => 'dark',
                'color'     => 'success',
            ],
			'facebook' => [
			],
			'google' => [
			],
		]
	],
    HOST_CLIENTS => [
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
                'name' 	  => 'Sommer Cukraszda',
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
                'name'      => 'ace',
                'version'   => 'dark',
                'color'     => 'success',
            ],
            'facebook' => [
            ],
            'google' => [
            ],
        ]
    ],
];

if(SERVER_ID === 'development'){
    $GLOBALS['HOSTS']['admin.sommer.test'] = $GLOBALS['HOSTS'][HOST_ADMIN];
    $GLOBALS['HOSTS']['sommer.test'] = $GLOBALS['HOSTS'][HOST_CLIENTS];
}