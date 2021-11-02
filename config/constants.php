<?php
const APPLICATION_NAME      = 'webshop';
const SERVER_TIME_ZONE      = 'UTC';
const DEFAULT_TIMEZONE_ID   = 29;            // (UTC+01:00) Belgrade, Bratislava, Budapest, Ljublj

// session keys
const SESSION_USER          = 'ws-userdata';
const SESSION_LOCALE        = 'ws-locale';
const SESSION_MESSAGES      = 'ws-messages';
const SESSION_HTACCESS      = 'ws-htaccess';

const HOST_SETTINGS         = APPLICATION_NAME . '-hosts-';
const LABELS_KEY            = APPLICATION_NAME . '-labels-';

// content cache keys
const CACHE_PAGES           = APPLICATION_NAME . '-pages-';
const CACHE_CATEGORIES      = APPLICATION_NAME . '-categories-';
const CACHE_SLIDERS         = APPLICATION_NAME . '-sliders-';
const CACHE_GALLERY         = APPLICATION_NAME . '-gallery-';
const CACHE_HIGHLIGHTS      = APPLICATION_NAME . '-highlights-';
const CACHE_POPULARS        = APPLICATION_NAME . '-popular-';
const CACHE_TAGGED          = APPLICATION_NAME . '-tagged-';
const CACHE_SETTINGS        = APPLICATION_NAME . '-settings-';

const CACHE_USER_PROFILE    = APPLICATION_NAME . '-userprofile-';

// cookie keys
const COOKIE_MACHINEID      = 'sc-mid';

const OUTPUT_HTML           = 'html';
const OUTPUT_JSON           = 'json';
const OUTPUT_RAW            = 'raw';

const FORM_STATE_INITED     = 'inited';
const FORM_STATE_LOADED     = 'loaded';
const FORM_STATE_REQUEST    = 'request';
const FORM_STATE_VALIDATED  = 'validated';
const FORM_STATE_INVALID    = 'invalid';
const FORM_STATE_SAVED      = 'saved';
const FORM_STATE_BUTTONACTION = 'buttonaction';
const FORM_STATE_RESETED    = 'reseted';

const VERSION_JS            = '1.0.2';
const VERSION_CSS           = '1.0.2';

//
/**
 * d - day of month (no leading zero)
 * dd - day of month (two digit)
 * D - day name short
 * DD - day name long
 * m - month of year (no leading zero)
 * mm - month of year (two digit)
 * M - month name short
 * MM - month name long
 * y - year (two digit)
 * yy - year (four digit)
 */
$GLOBALS['REGIONAL_SETTINGS'] = [
	'en' => [
		'name'          => 'English',
		'text'          => 'ltr',
		'firstday'      => 1,
		'dateformat'    => 'dd/mm/yy',
		'dateformat_short' => 'd. M.',
		'dateorder'     => 'dmy',
		'timeformat'    => '12',
		'decimal_point' => '.',
		'thousand_sep'  => ',',
		'currency_round'  => 1,
		'nameorder'     => 'first-last'
	],
	'hu' => [
		'name'          => 'Magyar',
		'text'          => 'ltr',
		'firstday'      => 1,
		'dateformat'    => 'yy-mm-dd',
		'dateformat_short' => 'M. d.',
		'dateorder'     => 'ymd',
		'timeformat'    => '24',
		'decimal_point' => ',',
		'thousand_sep'  => ' ',
		'currency_round'  => 0,
		'nameorder'     => 'last-first'
	],
];
$GLOBALS['REGIONAL_SETTINGS']['default'] = $GLOBALS['REGIONAL_SETTINGS']['hu'];


$GLOBALS['UPLOAD_IMG_FILES'] = ['jpg', 'png', 'jpeg'];
$GLOBALS['UPLOAD_DOC_FILES'] = ['txt', 'pdf', 'doc', 'docx'];

const TWIG_FILE_EXTENSION = '.twig';

$GLOBALS['PERSONAL_TITLES'] = [
	'MR',
	'MS',
	'MRS',
];

$GLOBALS['CURRENCIES'] = [
    'HUF' => [
        'sign' => 'Ft',
        'name' => 'forint',
        'round' => 0,
    ],
    'EUR' => [
        'sign' => '€',
        'name' => 'euro',
        'round' => 2,
    ],
    'USD' => [
        'sign' => '$',
        'name' => 'usdollar',
        'round' => 2,
    ],
];

const USER_GROUP_ADMINISTRATORS = 'ADMINISTRATORS';
const USER_GROUP_CUSTOMERS      = 'CUSTOMERS';

$GLOBALS['USER_GROUPS'] = [
    USER_GROUP_ADMINISTRATORS => [
		'label' => 'LBL_GROUP_ADMINISTRATORS',
		'color' => 'danger',
		'app'   => 'admin',
	],
    USER_GROUP_CUSTOMERS => [
		'label' => 'LBL_GROUP_CUSTOMERS',
		'color' => 'primary',
        'app'   => 'shop',
	],
];

const USER_ROLE_SUPERVISOR  = 'SUPERVISOR';
const USER_ROLE_ADMIN       = 'ADMIN';
const USER_ROLE_USER        = 'USER';
const USER_ROLE_NONE        = 'NONE';

$GLOBALS['USER_ROLES'] = [
    USER_GROUP_ADMINISTRATORS => [
        USER_ROLE_SUPERVISOR => [
            'label' => 'LBL_ROLE_SUPERVISOR',
            'color' => 'danger',
        ],
        USER_ROLE_ADMIN => [
            'label' => 'LBL_ROLE_ADMIN',
            'color' => 'warning',
        ],
    ],
    USER_GROUP_CUSTOMERS => [
        USER_ROLE_USER => [
            'label' => 'LBL_ROLE_USER',
            'color' => 'primary',
        ],
        USER_ROLE_NONE => [
            'label' => 'LBL_ROLE_NONE',
            'color' => 'primary',
        ],
    ],
];

const ACCESS_RIGHT_NO       = 0;
const ACCESS_RIGHT_READONLY = 1;
const ACCESS_RIGHT_WRITE    = 2;

const IP_CACHE_TIMEOUT      = 7; // Days
const CHUNK_SIZE            = 1024 * 1024;

const PROFILE_IMG_SIZE      = 200;

const FILEUPLOAD_MAX_FILES  = 9;
const FILEUPLOAD_MAX_FILESIZE = 20; // Mb

$GLOBALS['IMAGE_SIZES'] = [
    'default' => [
        'width'  => 1000,
        'height' => null,
        'crop' => false,
    ],
    'medium' => [
        'width'  => 500,
        'height' => 500,
        'crop' => true,
    ],
    'thumbnail' => [
        'width'  => 300,
        'height' => 300,
        'crop' => true,
    ],
];

const ORDER_STATUS_NEW = 'NEW';
const ORDER_STATUS_PROCESSING = 'PROCESSING';
const ORDER_STATUS_DELIVERING = 'DELIVERING';
const ORDER_STATUS_RECEIVABLE = 'RECEIVABLE';
const ORDER_STATUS_FINISHED = 'FINISHED';
const ORDER_STATUS_CLOSED = 'CLOSED';

$GLOBALS['ORDER_STATUSES'] = [
    ORDER_STATUS_NEW => [
        'name' => 'LBL_ORDER_STATUS_NEW',
        'class' => 'bg-danger text-white',
    ],
    ORDER_STATUS_PROCESSING => [
        'name' => 'LBL_ORDER_STATUS_PROCESSING',
        'class' => 'bg-warning text-white',
    ],
    ORDER_STATUS_DELIVERING => [
        'name' => 'LBL_ORDER_STATUS_DELIVERING',
        'class' => 'bg-success text-white',
    ],
    ORDER_STATUS_RECEIVABLE => [
        'name' => 'LBL_ORDER_STATUS_RECEIVABLE',
        'class' => 'bg-success text-white',
    ],
    ORDER_STATUS_FINISHED => [
        'name' => 'LBL_ORDER_STATUS_FINISHED',
        'class' => 'bg-primary text-white',
    ],
    ORDER_STATUS_CLOSED => [
        'name' => 'LBL_ORDER_STATUS_CLOSED',
        'class' => 'bg-secondary text-white',
    ],
];

$GLOBALS['PAGE_NAMES'] = [
    'hu' => [
        'register'  => [
            'name' => 'regisztracio',
            'display' => 0,
        ],
        'account'   => [
            'name' => 'fiokom',
            'display' => 0,
            'access' => true,
        ],
        'orders'   => [
            'name' => 'rendeleseim',
            'display' => 0,
            'access' => true,
        ],
        'products'  => [
            'name' => 'termekek',
            'display' => 1,
            'position' => 1,
            'header' => true,
            'footer' => false,
        ],
        'cart'      => [
            'name' => 'kosar',
            'display' => 0,
        ],
        'checkout'  => [
            'name' => 'megrendeles',
            'display' => 0,
        ],
        'finish'    => [
            'name' => 'rendeles',
            'display' => 0,
        ],
        'set-new-password' => [
            'name' => 'uj-jelszo',
            'display' => 0,
        ],
        'contact'   => [
            'name' => 'kapcsolat',
            'display' => 1,
            'header' => true,
            'footer' => true,
            'position' => 3,
        ]
    ],
    'en' => [
        'register'  => [
            'name' => 'register',
            'display' => 0,
        ],
        'account'   => [
            'name' => 'account',
            'display' => 0,
            'access' => true,
        ],
        'orders'   => [
            'name' => 'orders',
            'display' => 0,
            'access' => true,
        ],
        'products'  => [
            'name' => 'products',
            'header' => true,
            'footer' => false,
            'display' => 1,
            'position' => 1,
        ],
        'cart'      => [
            'name' => 'cart',
            'display' => 0,
        ],
        'checkout'  => [
            'name' => 'checkout',
            'display' => 0,
        ],
        'finish'    => [
            'name' => 'finish',
            'display' => 0,
        ],
        'set-new-password' => [
            'name' => 'set-new-password',
            'display' => 0,
        ],
        'contact'   => [
            'name' => 'contact',
            'display' => 1,
            'header' => true,
            'footer' => true,
            'position' => 3,
        ]
    ],
];