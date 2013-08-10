<?php
/*
 * This is the script for the profile page.
 */
session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
$css .= 'main.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,profile.js';
// only registered users can have a profile
if (isset($_SESSION['uid'])) {
    include INCLUDE_PATH . '/header.inc.php';
    ?>
    <style type="text/css">nav #profile{color:#8ad459}#profilePic{margin:45px}#profileFeedback{text-align:right}</style>
    <!--Main content-->
    <section id="content">
        <h1>Welcome, <?= htmlentities($_SESSION['username']) ?></h1>
        <!--Left half-->
        <div id="leftWrap">
            <!--Profile form-->
            <form id="profileForm" name="profileForm" method="POST" action="ajax/ajaxProfile.php">
                <div class="feedback" id="profileFeedback"></div>
                <label for="pass">Password <em>*</em></label>
                <input type="password" id="pass" name="pass" required="required" autocomplete="off" placeholder="Your current password" autofocus="autofocus"/><br/>
                <label for="newPass">New password</label>
                <input type="password" id="newPass" name="newPass" pattern="...+" autocomplete="off" placeholder="Your new password" title="At least 3 characters"/><br/>
                <label for="repeat">New password again</label>
                <input type="password" id="repeat" name="repeat" autocomplete="off" title="Passwords must match" placeholder="Did you memorize it?"/><br/>
                <label for="email">Email <em>*</em></label>
                <input type="email" id="email" name="email" autocomplete="on" placeholder="Your email" maxlength="256" required="required"
                       value="<?= htmlentities($_SESSION['email']) ?>"/><br/>
                <label for="fname">First name <em>*</em></label>
                <input type="text" id="fname" name="fname" required="required" placeholder="Your first name" pattern="^.*\S.*$" autocomplete="on" maxlength="32"
                       value="<?= htmlentities($_SESSION['firstname']) ?>"/><br/>
                <label for="lname">Last name</label>
                <input type="text" id="lname" name="lname" autocomplete="on" placeholder="Your last name" maxlength="32"
                       value="<?= htmlentities($_SESSION['lastname']) ?>"/><br/>
                <label><em>*</em> Required fields</label>
                <input type="button" id="delete" name="delete" value="Close acc." title="Close account"/>
                <input type="submit" id="save" name="save" value="Save"/>
            </form>
        </div>
        <!--Right half-->
        <div id="rightWrap">
            <!--Profile picture-->
            <img id="profilePic" class="album" src="<?= getPic($_SESSION['uid']) ?>" alt="Profile picture" title="Profile picture"/>
        </div>
    </section>
    <?php
    include INCLUDE_PATH . '/footer.inc.php';
} else {
    header('Location: login.php');
    exit;
}

// get profile picture by user ID
function getPic($uid) {
    // SELECT query
    $usel = "SELECT picture FROM users WHERE id=? LIMIT 1";
    $usel = dbQuery($usel, $uid);
    $urow = $usel->fetch();
    $upic = $urow['picture'];
    return $upic;
}
?>