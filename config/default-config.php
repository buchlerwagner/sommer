<?php
// directories
const DIR_WEB       = '/var/www/careline.hu/web/';
const DIR_CACHE     = '/var/www/careline.hu/cache/';
const DIR_UPLOAD    = '/var/www/careline.hu/uploads/';
const DIR_LOG       = '/var/www/careline.hu/logs/';

// Relative paths
const FOLDER_UPLOAD = '/uploads/';

// database
const DB_TYPE       = 'mysql';
const DB_ENCODING   = 'UTF8';

const DB_NAME_WEB   = 'careline';
const DB_HOST       = 'localhost';
const DB_USER       = 'tripcon';
const DB_PASSWORD   = 't1r2i3p4';

const MONGO_HOST    = 'localhost';
const MEMCACHE_HOST = 'localhost';

// memcache
const MEMCACHE_PORT = '11211';
const MEMCACHE_COMPRESS = false;

// mongo
const MONGO_PORT    = '27017';
const MONGO_USER    = '';
const MONGO_PASS    = '';
const MONGO_DATABASE = 'careline';

// server related
const SERVER_ID     = 'production';
const SET_AUTH      = false;
const AUTH_USER     = '';
const AUTH_PWD      = '';

const DEBUG_ON      = false;
const TWIG_CACHE_ENABLED = false;
const RELOAD_DICTIONARY = true;
const IMG_CACHE_ENABLED = false;

const EMAIL_SENDER_NAME     = 'careline.hu';
const EMAIL_SENDER_EMAIL    = 'no-reply@careline.hu';
const EMAIL_INSTANT_SEND    = false;
const EMAIL_USE_GMAIL_SMTP  = true;
const EMAIL_IMAP_HOST = 'imap.gmail.com';
const EMAIL_IMAP_PORT = 993;
const EMAIL_IMAP_SSL = true;
const EMAIL_USERNAME = 'wzs.dev@gmail.com';
const EMAIL_PASSWORD = 'wZs123DevMail2016';
const EMAIL_SMTP_PORT = 587;
const EMAIL_SMTP_HOST = 'smtp.gmail.com';

// default environment variables
const DEFAULT_HOST      = 'careline.hu';
const DEFAULT_COUNTRY   = 'HU';
const DEFAULT_LANGUAGE  = 'hu';
const DEFAULT_USE_SSL   = false;
const SESSION_ON_SUBDOMAINS = true;

const IPAPI_KEY     = '20966a71b200633664995f6b35b7e081';
const MAPS_API_KEY  = 'AIzaSyDE-NP3LID3iJDdFO2Do9Tpbv59T_UQQFU';

const HOST_ADMIN    = 'careline.hu';
const HOST_CLIENTS  = 'clients.careline.hu';


// SMS gateway
const SMS_ENDPOINT          = 'https://uzi.calgo.cc';
const SMS_USER              = '****.comnica.cc';
const SMS_PASSWORD          = '****';
const SMS_TEST_MODE         = true;