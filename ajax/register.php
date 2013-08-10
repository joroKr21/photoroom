<?php

/*
 * This script handles the server side for registration with ajax.
 */
session_start();
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/email.inc.php';
require_once INCLUDE_PATH . '/auth.inc.php';
require_once PHP_MAILER;
// if the user is already logged in, there is no need to register
if (isset($_SESSION['uid'])) {
    exit('<span class="error">You are already registered and logged in</span>');
} else if (isset($_POST['register'])) {
    echo register($_POST['username'], $_POST['password'], $_POST['email'], $_POST['firstname'], $_POST['lastname']);
}
?>