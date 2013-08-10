<?php
/*
 * This is the contact popup page.
 * 
 * Note that inline javascript has been used, because this is a popup page,
 * which makes it impossible to attach event handlers when the DOM is loaded,
 * unless we use the jQuery.live() function, which in turn reduces performance.
 * 
 * Also note that the contact.js script for this page is referenced in the
 * footer.php file, because this page is part of the footer.
 */
session_start();
require_once '../core.inc.php';
// if the user is logged in, autofill some fields
if (isset($_SESSION['uid'])) {
    $name = trim($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
    $email = $_SESSION['email'];
} else {
    $name = '';
    $email = '';
}
?>
<!doctype html>
<!--Contact-->
<html lang="en">
    <head>
        <title>Contact</title>
        <meta charset="utf-8" />
        <meta name="author" content="Georgi Krastev" />
        <link rel="stylesheet" type="text/css" href="/min/b=photoroom/css&amp;f=popup.css"/>
        <style type="text/css">
            div.center{text-align:center}
            #contactForm{color:black;text-align:right}
            #contactMessage{width:517px;resize:vertical}
            #characters{font-size:0.9em}
            #contactSubject{width:517px}
        </style>
    </head>
    <body class="popup">
        <h1 class="popup">Contact</h1>
        <div class="center">
            <form id="contactForm" name="contactForm" method="POST" action="ajax/ajaxContact.php">
                <div id="contactFeedback" class="feedback"></div>
                <label for="contactName">Your name <em>*</em></label>
                <input type="text" id="contactName" name="contactName" required="required" maxlength="65" placeholder="Your name" autocomplete="on" value="<?= $name ?>"/>
                <label for="contactName">Your email <em>*</em></label>
                <input type="email" id="contactEmail" name="contactEmail" required="required" maxlength="256" autocomplete="on" placeholder="Your email for reply"
                       value="<?= $email ?>"/><br/>
                <label for="contactSubject">Subject</label>
                <input type="text" id="contactSubject" name="contactSubject" maxlength="256" autocomplete="off" autofocus="autofocus" placeholder="Subject" pattern="[^\n]*"
                       list="subjects"/><br/>
                <datalist id="subjects">
                    <option value="Qestion">Question</option>
                    <option value="Feedback">Feedback</option>
                    <option value="Suggestion">Suggestion</option>
                    <option value="Rating">Rating</option>
                    <option value="Review">Review</option>
                    <option value="Report Abuse">Report Abuse</option>
                    <option value="Report Bug">Report Bug</option>
                </datalist>
                <label for="contactMessage">Your message <em>*</em></label>
                <label id="characters"> [1000 characters remaining]</label><br/>
                <textarea id="contactMessage" name="contactMessage" maxlength="2000" rows="10" required="required" placeholder="What do you have to say?"
                          onkeyup="charsRemaining();" onkeydown="charsRemaining();"></textarea><br/>
                <input type="button" id="refresh" value="Refresh" onclick="refreshCaptcha();"/>
                <img id="captcha" src="ajax/captcha.php" alt="Captcha"/>
                <div style="display:inline-block;vertical-align:top">
                    <label><em>*</em> Please type what you see</label><br/>
                    <input type="text" id="code" name="code" maxlength="8" autocomplete="off" pattern="[a-zA-Z0-9]+" placeholder="Captcha" required="required"/>
                </div><br/>
                <label><em>*</em> Required fields</label>
                <input type="submit" value="Send"/>
            </form>
        </div>
        <div class="right"><input type="button" value="Close" onclick="closePopup(300);"/></div>
    </body>
</html>
