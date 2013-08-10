<?php

/*
 * This is the view page.
 */
session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/pagination.inc.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
$css .= 'main.css,lightbox.css,mosaic.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,lightbox.js,mosaic-plugin-min.js,mosaic.js';
// if the user has the cookie
if (!isset($_SESSION['uid']) && isset($_COOKIE['username'])) {
    header('Location: login.php');
    exit;
} else if (!isset($_SESSION['uid']) && !isset($_GET['u'])) {
    header('Location: login.php');
    exit;
} else {
    include INCLUDE_PATH . '/header.inc.php';
}
?>
<style type="text/css">#view{color:#8ad459}</style>
<!--Main content-->
<section id="content">
    <?php

    // view.php?u=uid&a=aid
    if (isset($_GET['u'], $_GET['a'])) {
        // if the user is requesting his/her own album
        if (isset($_SESSION['uid']) and $_GET['u'] == $_SESSION['uid']) {
            showPhotos($_GET['u'], $_GET['a'], true);
        } else {
            showPhotos($_GET['u'], $_GET['a'], false);
        } // view.php?u=uid
    } else if (isset($_GET['u'])) {
        if (isset($_SESSION['uid']) and $_SESSION['uid'] == $_GET['u']) {
            showAlbums($_GET['u'], true);
        } else {
            showAlbums($_GET['u'], false);
        }
    } else if (isset($_SESSION['uid'])) { // view.php
        showAlbums($_SESSION['uid'], true);
    }

    echo '</section>';
    include INCLUDE_PATH . '/footer.inc.php';

    // show photos
    function showPhotos($uid, $aid, $showall) {
        // SELECT query
        $pnum = "SELECT COUNT(*) AS num FROM photos WHERE album_id=?";
        $pnum = dbQuery($pnum, $aid);
        $presult = $pnum->fetch();
        // paginate
        $count = $presult['num'];
        $pages = ceil($count / PERPAGE);
        $current = (isset($_GET['page']) and $_GET['page'] > 0 and $_GET['page'] <= $pages) ? $_GET['page'] : 1;
        $start = ($current - 1) * PERPAGE;
        $perPage = PERPAGE;

        if (isset($_GET['page'])) {
            $target = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - strlen($_GET['page']) - 6);
        } else {
            $target = $_SERVER['REQUEST_URI'];
        }
        // if the user has permission to view private photos
        if ($showall) {
            // SELECT query
            $asel = "SELECT title, description, cover FROM albums WHERE id=? LIMIT 1";
        } else {
            // SELECT query
            $asel = "SELECT title, description, cover FROM albums WHERE id=? AND visibility=1 LIMIT 1";
        }
        // SELECT query
        $psel = "SELECT id, filename, description, date, category FROM photos WHERE album_id=? ORDER BY date DESC LIMIT $start, $perPage";
        $asel = dbQuery($asel, $aid);
        $arow = $asel->fetch();
        // check we have results
        if (empty($arow)) {
            exit('<div class="feedback"><span class="error">Nothing to show here</span></div>');
        } else { // retrieve data
            $atitle = htmlentities($arow['title']);
            $adesc = htmlentities($arow['description']);
            $acover = $arow['cover'];
        }

        $psel = dbQuery($psel, $aid);
        $presult = $psel->fetchAll();
        // paginate
        if ($pages > 1) {
            $from = $start + 1;
            $to = $start + PERPAGE;
            $to = ($to > $count) ? $count : $to;
            $showing = "$from-$to of ";
        } else {
            $showing = '';
        }
        // display
        echo "<h1>$atitle</h1><a href=\"view.php?u=$uid\">back to albums</a><h3>[$showing$count photos]</h3>",
        ($adesc !== '') ? "<p>$adesc</p><hr/>" : '', paginate($target, $current, $pages),
        "<br/><img class=\"album\" src=\"$acover\" alt=\"$adesc\" title=\"Album cover\"/>";
        // display all photos
        foreach ($presult as $prow) {
            // retrieve data
            $thumb = $prow['filename'];
            $src = str_replace('thumbs/', '', $thumb);
            $pdesc = htmlentities($prow['description']);
            $pdescription = ($pdesc !== '') ? "<textarea rows=\"3\" disabled=\"disabled\">$pdesc</textarea><br/>" : '';
            $pdate = htmlentities($prow['date']);
            $pcat = htmlentities($prow['category']);
            $pcategory = ($pcat !== '') ? "[<a href=\"index.php?c=$pcat\">$pcat</a>]<br/>" : '';
            // display
            echo "<div class=\"mosaic-block bar\"><div class=\"mosaic-overlay\"><div class=\"details\">"
        . "<a href=\"$src\" rel=\"lightbox[$atitle]\" caption=\"[$pcat]:$pdate: $pdesc\">Large</a>"
        . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pdate<br/>$pcategory $pdescription</div></div>"
        . "<a href=\"$src\" rel=\"lightbox[$atitle.2]\" caption=\"[$pcat]:$pdate: $pdesc\"><img src=\"$thumb\"/></a></div>";
        }

        echo '<br/>', paginate($target, $current, $pages);
    }

    // show albums
    function showAlbums($uid, $showall) {
        // if the user has permission to view private albums
        if ($showall) {
            // SELECT query
            $anum = "SELECT COUNT(*) AS num FROM albums WHERE author_id=?";
        } else {
            // SELECT query
            $anum = "SELECT COUNT(*) AS num FROM albums WHERE author_id=? and visibility=1";
        }

        $anum = dbQuery($anum, $uid);
        $aresult = $anum->fetch();
        // paginate
        $count = $aresult['num'];
        $pages = ceil($count / PERPAGE);
        $current = (isset($_GET['page']) and $_GET['page'] > 0 and $_GET['page'] <= $pages) ? $_GET['page'] : 1;
        $start = ($current - 1) * PERPAGE;
        $perPage = PERPAGE;

        if (isset($_GET['page'])) {
            $target = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - strlen($_GET['page']) - 6);
        } else {
            $target = $_SERVER['REQUEST_URI'];
        }
        // SELECT query
        $usel = "SELECT firstname, lastname, picture FROM users WHERE id=? LIMIT 1";
        // if the user has permission to view private albums
        if ($showall) {
            // SELECT query
            $asel = "SELECT id, title, description, cover FROM albums WHERE author_id=? ORDER BY title COLLATE utf8_unicode_ci LIMIT $start, $perPage";
        } else {
            // SELECT query
            $asel = "SELECT id, title, description, cover FROM albums WHERE author_id=? AND visibility=1 ORDER BY title COLLATE utf8_unicode_ci LIMIT $start, $perPage";
        }

        $usel = dbQuery($usel, $uid);
        $urow = $usel->fetch();
        // check we have results
        if (empty($urow)) {
            exit('<div class="feedback"><span class="error">Nothing to show here</span></div>');
        } else { // retrieve data
            $ufirst = htmlentities($urow['firstname']);
            $ulast = htmlentities($urow['lastname']);
            $upic = $urow['picture'];
        }

        $asel = dbQuery($asel, $uid);
        $aresult = $asel->fetchAll();
        // paginate
        if ($pages > 1) {
            $from = $start + 1;
            $to = $start + PERPAGE;
            $to = ($to > $count) ? $count : $to;
            $showing = "$from-$to of ";
        } else {
            $showing = '';
        }
        // display
        echo "<h1>$ufirst $ulast</h1><img src=\"ajax/emailImg.php?uid=$uid\" alt=\"email\"/><h3>[$showing$count albums]</h3>",
        paginate($target, $current, $pages),
        "<br/><img class=\"album\" src=\"$upic\" alt=\"$ufirst $ulast\" title=\"Profile picture\"/>";
        // display albums
        foreach ($aresult as $arow) {
            // retrieve data
            $aid = $arow['id'];
            $atitle = htmlentities($arow['title']);
            $adesc = htmlentities($arow['description']);
            $adescription = "<br/><textarea rows=\"3\" disabled=\"disabled\">$adesc</textarea>";
            $acover = $arow['cover'];
            // display
            echo "<a href=\"view.php?u=$uid&a=$aid\"><div class=\"mosaic-block bar2\"><div class=\"mosaic-overlay\">"
            . "<div class=\"details\">$atitle $adescription</div></div><img src=\"$acover\" alt=\"$adesc\"/></div></a>";
        }

        echo '<br/>', paginate($target, $current, $pages);
    }
    ?>