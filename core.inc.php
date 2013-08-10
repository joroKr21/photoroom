<?php

/*
 * This file includes core variables and
 * configuration changes to the php settings.
 * 
 * IMPORTANT: must be included in every php file
 * Ideally, place in a folder, that is not accessible from the internet
 */
// path variables
// NOTE: change these variables according to your installation
define('DOMAIN', 'http://' . $_SERVER['SERVER_NAME'] . '/photoroom');
define('INCLUDE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/photoroom/include');
define('DB_PATH', $_SERVER['DOCUMENT_ROOT'] . '/photoroom/db');
define('FONTS_PATH', $_SERVER['DOCUMENT_ROOT'] . '/photoroom/fonts');
define('PHP_MAILER', $_SERVER['DOCUMENT_ROOT'] . '/phpmailer/class.phpmailer.php');
define('STORE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/photoroom/photos');
define('PHOTOS_URL', DOMAIN . '/photos');
// debug
define('DEBUG', true);
// CSS and JavaScript paths
$js = '/min/b=photoroom/js&amp;f=';
$css = '/min/b=photoroom/css&amp;f=';
// database variables
// NOTE: change these variables according to your database server
define('DB_HOST', 'localhost');
define('DB_NAME', 'photoroom');
define('DB_USER', 'root');
define('DB_PASS', '');
// email variables
// NOTE: change these variables according to your email server
define('EMAIL_ADDR', 'joro.kr.21@gmail.com');
define('EMAIL_NAME', 'PhotoRoom');
define('EMAIL_PASS', 'th1s1sspar7a');
define('EMAIL_HOST', 'ssl://smtp.gmail.com');
define('EMAIL_PORT', 465);
// configuration changes to the php settings
// WARNING: Do not modify!
ini_set('short_open_tag', '1');
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '16M');
if (DEBUG) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}
?>