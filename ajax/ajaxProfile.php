<?php

/*
 * This script handles the server side for editing user profiles with ajax.
 */
session_start();
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/profile.inc.php';
require_once INCLUDE_PATH . '/rrmdir.inc.php';
require_once INCLUDE_PATH . '/auth.inc.php';
// only registered users can make profile changes
if (isset($_SESSION['uid'])) {
    // save profile changes
    if (isset($_POST['save'])) {
        echo saveChanges($_SESSION['uid'], $_POST['newPass'], $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_SESSION['username'], $_POST['password']);
    } else if (isset($_POST['deleteAcc'])) {
        echo deleteAcc($_SESSION['uid'], $_SESSION['username'], $_POST['password']);
    }
}
?>