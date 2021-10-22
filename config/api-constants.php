<?php
const API_CURRENT_VERSION           = '1.0.0';
const API_DEFAULT_LANGUAGE          = 'en';

const API_REQUEST                   = 'RQ';
const API_RESPONSE                  = 'RS';

/**
 * API HTTP response codes
 */
const API_HTTP_OK                   = 200;
const API_HTTP_BAD_REQUEST          = 400;
const API_HTTP_UNAUTHORIZED         = 401;
const API_HTTP_FORBIDDEN            = 403;
const API_HTTP_NOT_FOUND            = 404;
const API_HTTP_METHOD_NOT_ALLOWED   = 405;
const API_HTTP_CONFLICT             = 409;
const API_HTTP_UNSUPPORTED_MEDIA    = 415;

const API_REQUEST_INTERVAL          = 5;        // min sec between 2 request
const API_LIMIT_REQUEST_FREQUENCY   = true;     // limit number of requests

const API_MAX_LOGIN_ATTEMPT         = 5;        // max allowed failed login attempt
const API_LOG_REQUESTS              = true;     // log requests
const API_LOG_RESPONSES             = true;     // log responses
const API_LOG_EXCEPTIONS            = true;     // create log about exception

const API_AUTH_TYPE_NONE            = 0;
const API_AUTH_TYPE_BASIC           = 1;
const API_AUTH_TYPE_APIKEY          = 2;
const API_AUTH_TYPE_TOKEN           = 4;

$GLOBALS['API_LANGUAGES'] = [
    'en'
];

$GLOBALS['API_VALID_VERSIONS'] = [
    API_CURRENT_VERSION
];

$GLOBALS['API_SERVICES'] = [
    'dictionary' => [
        API_CURRENT_VERSION => [
            'class' => 'dictionaryService',
            'auth' => API_AUTH_TYPE_BASIC
        ]
    ],
    'test' => [
        API_CURRENT_VERSION => [
            'class' => 'testService',
            'auth' => API_AUTH_TYPE_NONE
        ]
    ],
];