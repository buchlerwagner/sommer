<?php

// directories
const DIR_WEB               = '/var/www/sommer-cukraszda.hu/web/';
const DIR_CACHE             = '/var/www/sommer-cukraszda.hu/cache/';
const DIR_UPLOAD            = '/var/www/sommer-cukraszda.hu/web/public/uploads/';
const DIR_LOG               = '/var/www/sommer-cukraszda.hu/logs/';

// Relative paths
const FOLDER_UPLOAD         = '/uploads/';

// database
const DB_TYPE               = 'mysql';
const DB_ENCODING           = 'UTF8';

const DB_NAME_WEB           = 'sommer';
const DB_HOST               = 'localhost';
const DB_USER               = '';
const DB_PASSWORD           = '';

// memcache
const MEMCACHE_HOST         = 'localhost';
const MEMCACHE_PORT         = '11211';
const MEMCACHE_COMPRESS     = false;

// server related
const SERVER_ID             = 'production';
const SET_AUTH              = false;
const AUTH_USER             = '';
const AUTH_PWD              = '';

const DEBUG_ON              = false;
const TWIG_CACHE_ENABLED    = false;
const RELOAD_DICTIONARY     = true;
const IMG_CACHE_ENABLED     = false;

const EMAIL_SENDER_NAME     = 'sommer-cukraszda.hu';
const EMAIL_SENDER_EMAIL    = 'no-reply@sommer-cukraszda.hu';
const EMAIL_INSTANT_SEND    = true;
const EMAIL_USE_GMAIL_SMTP  = true;
const EMAIL_USERNAME        = 'wzs.dev@gmail.com';
const EMAIL_PASSWORD        = 'wZs975DevMail2021';
const EMAIL_SMTP_PORT       = 587;
const EMAIL_SMTP_HOST       = 'smtp.gmail.com';

// default environment values
const DEFAULT_HOST          = 'sommer-cukraszda.hu';
const DEFAULT_COUNTRY       = 'HU';
const DEFAULT_LANGUAGE      = 'hu';
const SESSION_ON_SUBDOMAINS = true;

const HOST_ADMIN            = 'admin.sommer-cukraszda.hu';
const HOST_CLIENTS          = 'sommer-cukraszda.hu';
