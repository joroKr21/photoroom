<?php

/*
 * This file handles contact submission with ajax.
 */
session_start();
require_once '../core.inc.php';
require_once INCLUDE_PATH . '/email.inc.php';
require_once PHP_MAILER;
// contact form submitted
if (isset($_POST['contact'])) {
    echo contact();
}

// contact
function contact() {
    // retieve data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim(str_replace('\n', ' ', $_POST['subject']));
    $message = trim($_POST['message']);
    $captcha = strtolower(trim($_POST['captcha']));
    // verify captcha
    if (!isset($_SESSION['captcha']) || $_SESSION['captcha'] != $captcha) {
        return '<span class="error">Wrong captcha, try again</span>';
    }
    // validate data
    if ($name === '' || strlen($name) > 65) {
        return '<span class="error">Invalid name</span>';
    } else if ($email === '' || strlen($email) > 256 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '<span class="error">Invalid email</span>';
    } else if ($message === '' || strlen($message) > 1000) {
        return '<span class="error">Invalid message</span>';
    } else if (strlen($subject) > 256) {
        return '<span class="error">Invalid subject</span>';
    }
    // data is valid, send email
    if (email($email, $name, EMAIL_ADDR, EMAIL_NAME, false, $message, $subject)) {
        return '<span class="success">Your message has been sent<br/>Thank you for contacting us</span>';
    } else {
        return '<span class="error">An error has occured<br/>Please try again later</span>';
    }
}

?>
