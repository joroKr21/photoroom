<?php

/*
 * This is the authentication module.
 * NOTE: must include email.inc.php
 */

// register user
function register($uname, $upass, $email, $ufirst, $ulast) {
    global $db;
    // validate input
    if ($uname === '' || preg_match('[^a-zA-Z0-9.-_]', $uname)) {
        return '<span class="error">Invalid username</span>';
    } else if (strlen($upass) < 3) {
        return '<span class="error">Invalid password</span>';
    } else if ($email === '' || strlen($email) > 256 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '<span class="error">Invalid email</span>';
    } else if ($ufirst === '') {
        return '<span class="error">Invalid first name</span>';
    }

    try {
        // begin database transaction
        $db->beginTransaction();
        // SELECT query
        $usel = "SELECT id FROM users WHERE username=? LIMIT 1";
        // check username
        $usel = dbQuery($usel, $uname);
        $urow = $usel->fetch();
        if (!empty($urow)) {
            return '<span class="error">Username already taken</span>';
        }
        // SELECT query
        $usel = "SELECT id FROM users WHERE email=? LIMIT 1";
        // check email
        $usel = dbQuery($usel, $email);
        $urow = $usel->fetch();
        if (!empty($urow)) {
            return '<span class="error">Email already in use</span>';
        }
        // create a random salt
        $salt = hash('sha256', rand());
        // INSERT query
        $uins = "INSERT INTO users (username, password, email, firstname, lastname, salt) VALUES(?,?,?,?,?,?)";
        // insert data and get user ID
        dbQuery($uins, $uname, hash('sha256', $upass . $salt), $email, $ufirst, $ulast, $salt);
        $uid = dbInsertID();
        // confirm registration message
        $message = "Registration Confirmation at PhotoRoom:
--------------------------------------------------------------------------------
Thank you for registering at PhotoRoom, $ufirst $ulast!
Now you can use your username or email and password to login.

This email was automatically generated.";
        // try creating folder or rollback
        if (!is_dir(STORE_PATH . "/$uid") && !mkdir(STORE_PATH . "/$uid", 0777, true)) {
            // rollback database transaction
            $db->rollBack();
            return '<span class="error">Failed to create folder</span>';
        } else {
            // end database transaction
            $db->commit();
            // send mail to confirm registration
            email(EMAIL_ADDR, EMAIL_NAME, $email, $uname, false, $message, 'Registration Confirmation');
            return '<span class="success">Registration Successful<br/>Now you can login</span>';
        }
    } catch (PDOException $e) {
        prettyDie('Database Error: ' . $e->getMessage());
    }
}

// authenticate user against databse
function auth($uname, $upass) {
    // SELECT query
    $usel = "SELECT salt FROM users WHERE username=? LIMIT 1";
    $usel = dbQuery($usel, $uname);
    $urow = $usel->fetch();

    if (empty($urow)) {
        return false;
    } else {
        $salt = $urow['salt'];
    }

    // SELECT query
    $usel = "SELECT id FROM users WHERE (username=? OR email=?) AND password=? LIMIT 1";
    $usel = dbQuery($usel, $uname, $uname, hash('sha256', $upass . $salt));
    $urow = $usel->fetch();
    // check credentials
    if (empty($urow)) {
        return false;
    } else {
        return true;
    }
}

// authenticate and get user data
function getUserData($uname, $upass) {
    // SELECT query
    $usel = "SELECT salt FROM users WHERE username=? LIMIT 1";
    $usel = dbQuery($usel, $uname);
    $urow = $usel->fetch();

    if (empty($urow)) {
        return false;
    } else {
        $salt = $urow['salt'];
    }

    // SELECT query
    $usel = "SELECT id, username, email, firstname, lastname, level FROM users WHERE (username=? OR email=?) AND password=? LIMIT 1";
    $usel = dbQuery($usel, $uname, $uname, hash('sha256', $upass . $salt));
    $urow = $usel->fetch();
    // check credentials
    if (empty($urow)) {
        return false;
    } else {
        return $urow;
    }
}

// logout
function logout() {
    if (!empty($_SESSION)) {
        // delete cookies
        deleteCookies();
        // unset session variables
        unset($_SESSION['uid']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['firstname']);
        unset($_SESSION['lastname']);
        unset($_SESSION['style']);
        unset($_SESSION['level']);
        // destroy session
        session_destroy();
    }
}

// set cookies
function setCookies($remember) {
    // determine expire time
    if ($remember) {
        $expire = time() + 60 * 60 * 24 * 365;
    } else {
        $expire = 0;
    }
    // generate a random token
    $token = md5(rand());
    // UPDATE query
    $uupd = "UPDATE users SET token=? WHERE id=? LIMIT 1";
    dbQuery($uupd, hash('sha256', $token), $_SESSION['uid']);
    // set cookies
    setcookie('username', $_SESSION['username'], $expire);
    setcookie('token', $token, $expire);
    setcookie('style', $_SESSION['style'], $expire);
}

// delete cokies
function deleteCookies() {
    $expire = time() - 60 * 60 * 24 * 365;
    // UPDATE query
    $uupd = "UPDATE users SET token=? WHERE id=? LIMIT 1";
    dbQuery($uupd, ' ', $_SESSION['uid']);
    // delete cookies
    setcookie('username', '', $expire);
    setcookie('token', '', $expire);
    setcookie('style', '', $expire);
}

// login with cookies
function cookieAuth() {
    if (isset($_COOKIE['username']) && isset($_COOKIE['token'])) {
        // SELECT query
        $usel = "SELECT token FROM users WHERE username=? LIMIT 1";
        $usel = dbQuery($usel, $_COOKIE['username']);
        $urow = $usel->fetch();
        // if login successful
        if (!empty($urow) && $urow['token'] == hash('sha256', $_COOKIE['token'])) {
            // SELECT query
            $usel = "SELECT id, email, firstname, lastname, level FROM users WHERE username=? LIMIT 1";
            $usel = dbQuery($usel, $_COOKIE['username']);
            $urow = $usel->fetch();
            // set session variables
            $_SESSION['uid'] = $urow['id'];
            $_SESSION['username'] = $_COOKIE['username'];
            $_SESSION['email'] = $urow['email'];
            $_SESSION['firstname'] = $urow['firstname'];
            $_SESSION['lastname'] = $urow['lastname'];
            $_SESSION['level'] = $urow['level'];
            // reset the token
            setCookies(true);
            // determine the selected color scheme
            if (isset($_COOKIE['style'])) {
                $_SESSION['style'] = $_COOKIE['style'];
            } else {
                $_SESSION['style'] = 'dark';
            }
            // redirect
            if (isset($_SESSION['location'])) {
                header('Location: ' . $_SESSION['location']);
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}

// reset a password
function resetPassword($uname, $email) {
    // SELECT query
    $usel = "SELECT id FROM users WHERE username=? AND email=? LIMIT 1";
    // UPDATE query
    $uupd = "UPDATE users SET password=?, salt=? WHERE id=? LIMIT 1";
    $usel = dbQuery($usel, $uname, $email);
    $urow = $usel->fetch();

    if (empty($urow)) {
        return '<span class="error">Username or email do not match</span>';
    } else {
        $uid = $urow['id'];
        // create a random password
        $randomPass = substr(md5(rand()), 0, 8);
        // create random salt
        $salt = hash('sha256', rand());
        // message to be sent
        $message = "Your new password at PhotoRoom is as follows:
--------------------------------------------------------------------------------
Password: $randomPass
--------------------------------------------------------------------------------
If you do not have a registration at PhotoRoom, plase ignore this email.
--------------------------------------------------------------------------------
Otherwise, please change your password as soon as possible for security concerns.

This email was automatically generated.";
        // try sending an email
        if (!email(EMAIL_ADDR, EMAIL_NAME, $email, $uname, false, $message, 'Password Reset')) {
            return '<span class="error">An email could not be sent to your address</span>';
        } else {
            dbQuery($uupd, hash('sha256', $randomPass . $salt), $salt, $uid);
            return '<span class="success">Your password was reset and sent to your email</span>';
        }
    }
}

?>
