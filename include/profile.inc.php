<?php

/*
 * This script contains functions for progile editing.
 * NOTE: must include auth.inc.php and rrmdir.inc.php
 */

// save profile changes
function saveChanges($uid, $unew, $email, $ufirst, $ulast, $uname, $upass) {
    // validate user credentials
    if (auth($uname, $upass)) {
        // check if email is available
        if ($email != $_SESSION['email']) {
            // SELECT query
            $usel = "SELECT id FROM users WHERE email=?";
            $usel = dbQuery($usel, $email);
            $uresult = $usel->fetchAll();
            
            if (!empty($uresult)) {
                return '<span class="error">Email already used</span>';
            }
        }
        // UPDATE query
        $upd = "UPDATE users SET password=?, email=?, firstname=?, lastname=?, salt=? WHERE id=? LIMIT 1";
        // validate input
        if (strlen($unew) < 3) {
            $unew = $_POST['password'];
        }

        $ufirst = trim($ufirst);
        if ($ufirst !== '') {
            $_SESSION['firstname'] = $ufirst;
        } else {
            $ufirst = $_SESSION['firstname'];
        }

        if (strlen($email) <= 256 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['email'] = $email;
        } else {
            $email = $_SESSION['email'];
        }

        $ulast = trim($ulast);
        $_SESSION['lastname'] = $ulast;
        // create a random salt
        $salt = hash('sha256', rand());

        dbQuery($upd, hash('sha256', $unew . $salt), $email, $ufirst, $ulast, $salt, $uid);
        return '<span class="success">Your changes have been saved</span>';
    } else {
        return '<span class="error">Wrong password</span>';
    }
}

// delete account by user ID
function deleteAcc($uid, $uname, $upass) {
    global $db;
    // validate user credentials
    if (auth($uname, $upass)) {
        // delete all files, associated with this account
        if (is_dir(STORE_PATH . "/$uid")) {
            rrmdir(STORE_PATH . "/$uid");
        }
        // DELETE query
        $pdel = "DELETE FROM photos WHERE author_id=?";
        // DELETE query
        $adel = "DELETE FROM albums WHERE author_id=?";
        // DELETE query
        $udel = "DELETE FROM users WHERE id=? LIMIT 1";
        dbQuery($pdel, $uid);
        dbQuery($adel, $uid);
        dbQuery($udel, $uid);
        // logout
        logout();
        return '<span class="success">Your account has been deleted</span>';
    } else {
        return '<span class="error">Wrong password</span>';
    }
}

?>
