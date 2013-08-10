<?php
/*
 * This is the administrstion page.
 */
session_start();
require_once 'core.inc.php';
require_once INCLUDE_PATH . '/rb.php';
require_once DB_PATH . '/db.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
$css .= 'main.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,admin.js';
// allow only administrators to enter this page
if ((!isset($_SESSION['uid']) || $_SESSION['level'] <= 0)) {
    header('Location: login.php');
    exit;
}

include INCLUDE_PATH . '/header.inc.php';
?>
<style type="text/css">#sql,#tables{width:300px}#tables{margin-bottom:10px}</style>
<section id="content">
    <h1>Administration [under construction]</h1>
    <div id="leftWrap">
        <form id="backupForm" name="backupForm" method="POST" action="ajax/backupDB.php">
            <select id="tables" name="tables" multiple="multiple" title="Choose tables to backup (default: all)"><? showTables(); ?></select>
            <br/><input type="submit" id="backup" name="backup" value="Backup DB" title="Backup selected tables from the database"/>
        </form>
    </div>
    <div id="rightWrap">
        <form id="query" name="query" method="POST" action="ajax/query.php">
            <textarea rows="4" id="sql" name="sql" required="required" autofocus="autofocus" placeholder="Enter your SQL query here"></textarea><br/>
            <input type="button" id="clean" name="clean" value="Clean HD" title="Clean junk image files on this server"/>
            <input type="submit" id="go" name="go" value="SQL Query" title="Execute an SQL query on the database"/>
        </form>
    </div>
    <div class="feedback" id="adminFeedback"></div>
</section>
<?php
include INCLUDE_PATH . '/footer.inc.php';

// function to show all tables
function showTables() {
    $sql = "SHOW TABLES";
    $sql = dbQuery($sql);
    $sql->setFetchMode(PDO::FETCH_NUM);
    $result = $sql->fetchAll();

    foreach ($result as $row) {
        echo "<option value=\"$row[0]\">$row[0]</option>";
    }
}
?>