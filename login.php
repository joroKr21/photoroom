<?php
/*
 * This is the script for the login / register page
 */
session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/auth.inc.php';
require_once INCLUDE_PATH . '/email.inc.php';
require_once PHP_MAILER;
$css .= 'main.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,login.js';
// This variable will be set to false if the login is unsuccessful.
$login = '';
// If the user wants to logout, unset all session variables and destroy session.
if (isset($_GET['logout'])) {
    logout();
}
// login with cookies if possible
if (!empty($_COOKIE)) {
    cookieAuth();
}
// login
if (isset($_POST['login'])) {
    $urow = getUserData($_POST['loginUname'], $_POST['loginPass']);
    // if login successful
    if ($urow) {
        // set session variables
        $_SESSION['uid'] = $urow['id'];
        $_SESSION['username'] = $urow['username'];
        $_SESSION['email'] = $urow['email'];
        $_SESSION['firstname'] = $urow['firstname'];
        $_SESSION['lastname'] = $urow['lastname'];
        $_SESSION['level'] = $urow['level'];
        // determine the selected color scheme
        if (isset($_POST['lights'])) {
            $_SESSION['style'] = 'light';
        } else {
            $_SESSION['style'] = 'dark';
        }
        // set cookies
        setCookies(isset($_POST['remember']));
        // redirect
        if (isset($_SESSION['location'])) {
            header('Location: ' . $_SESSION['location']);
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $login = '<span class="error">Username or password do not match</span>';
    }
}
// reset password
if (isset($_POST['reset'])) {
    $reset = resetPassword($_POST['username'], $_POST['email']);
} else {
    $reset = '';
}

include INCLUDE_PATH . '/header.inc.php';
?>
<style type="text/css">#loginFeedback{text-align:right}#regFeedback{text-align:left}#forgotten{margin-right:10px}</style>
<!--Main content-->
<section id="content">
    <!--Left half-->
    <div id="leftWrap">
        <!--Login form-->
        <form id="loginForm" name="loginForm" method="POST" action="login.php">
            <div class="feedback" id="loginFeedback"><?= $login . $reset ?></div>
            <label for="loginUname">Username / Email</label>
            <input type="text" id="loginUname" name="loginUname" required="required" autocomplete="on" autofocus="autofocus"/><br/>
            <label for="loginPass">Password</label>
            <input type="password" id="loginPass" name="loginPass" autocomplete="off" required="required"/><br/>
            <label for="remember">Remember me</label>
            <input type="checkbox" id="remember" name="remember"/><br/>
            <label for="lights">Lights on</label>
            <input type="checkbox" id="lights" name="lights"/><br/>
            <input type="submit" id="login" name="login" value="Login"/>
            <br/><br/><br/><br/><br/><br/>
            <a id="forgotten" href="popup/forgotten.min.html">Forgot your password?</a>
        </form>
    </div>
    <!--Right half-->
    <div id="rightWrap">
        <!--Registration form-->
        <form id="regForm" name="regForm" method="POST" action="ajax/register.php">
            <div class="feedback" id="regFeedback"></div>
            <input type="text"  id="regUname" name="regUname" required="required" pattern="^[a-zA-Z0-9_.-]+$" placeholder="Desired username" maxlength="32" autocomplete="off"
                   title="Only letters, numbers and . , - , _"/>
            <label for="regUname"><em>*</em> Username</label><br/>
            <input type="password" id="regPass" name="regPass" required="required" pattern="...+" placeholder="Do not use your birthday!" autocomplete="off" title="At least 3 characters"/>
            <label for="regPass"><em>*</em> Password</label><br/>
            <input type="password" id="regRepeat" name="regRepeat" required="required" autocomplete="off" title="Passowords must match" placeholder="Sure you remember it?"/>
            <label for="regRepeat"><em>*</em> Password again</label><br/>
            <input type="email" id="regEmail" name="regEmail" required="required" maxlength="256" autocomplete="on" placeholder="Receive confirmation"/>
            <label for="regEmail"><em>*</em> Email</label><br/>
            <input type="text" id="regFname"  name="regFname" required="required" pattern="^.*\S.*$" autocomplete="on" placeholder="Your first name" maxlength="32"/>
            <label for="regFname"><em>*</em> First name</label><br/>
            <input type="text" id="regLname" name="regLname" autocomplete="on" placeholder="Your last name" maxlength="32"/>
            <label for="regLname">Last name</label><br/>
            <input type="checkbox" id="terms" name="terms" required="required" checked="checked"/>
            <label for="terms"><em>*</em> I have read and accept the
                <a id="conditions" href="popup/terms.min.html">Terms and conditions</a>
            </label><br/>
            <input type="submit" id="register" name="register" value="Register"/>
            <label><em>*</em> Required fields</label>
        </form>
    </div>
</section>
<?php include INCLUDE_PATH . '/footer.inc.php'; ?>
