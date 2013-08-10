<?php
/*
 * This is the edit page.
 */

session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/pagination.inc.php';
require_once INCLUDE_PATH . '/categories.inc.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
$css .= 'main.css,edit.css,mosaic.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,edit.js,mosaic-plugin-min.js,mosaic.js';
// allow only registered users to see this page
if (isset($_SESSION['uid'])) {
    include INCLUDE_PATH . '/header.inc.php';
    ?>
    <!--Context menu-->
    <aside id="sidebar">
        <!--Edit image form-->
        <form id="editPic" name="editPic" method="POST" action="ajax/ajaxEdit.php">
            <label for="category">Category:</label><br/>
            <select id="category" name="category"><?= listCategories(); ?></select><br/>
            <label for="description">Description:</label><br/>
            <textarea id="description" name="description" rows="3" placeholder="Photo description"></textarea><br/>
            <input type="checkbox" id="profilePic" name="profilePic"/>
            <label for="profilePic">Profile picture</label><br/>
            <input type="checkbox" id="albumCover" name="albumCover"/>
            <label for="albumCover">Album cover</label><br/>
            <input type="button" id="deletePic" name="deletePic" value="Delete"/>
            <input type="submit" id="savePic" name="savePic" value="Save"/>
            <div class="feedback" id="editPicFeedback"></div>
        </form>
    </aside>
    <!--Main content-->
    <section id="content">
        <?php
        // show album or photos
        if (isset($_GET['a'])) {
            showPhotos($_SESSION['uid'], $_GET['a']);
        } else {
            showAlbums($_SESSION['uid']);
        }
        ?>
    </section>
    <?php
    include INCLUDE_PATH . '/footer.inc.php';
} else {
    header('Location: login.php');
    exit;
}

// show photos by album ID and user ID
function showPhotos($uid, $aid) {
    // SELECT query
    $pnum = "SELECT COUNT(id) AS num FROM photos WHERE album_id=? AND author_id=?";
    $pnum = dbQuery($pnum, $aid, $uid);
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
    // SELECT query
    $asel = "SELECT id, title, description, cover, visibility FROM albums WHERE id=? AND author_id=? LIMIT 1";
    // SELECT query
    $psel = "SELECT id, filename, description, date, category FROM photos WHERE album_id=? AND author_id=? ORDER BY date DESC LIMIT $start, $perPage";
    $asel = dbQuery($asel, $aid, $uid);
    $arow = $asel->fetch();
    // album deleted
    if (empty($arow)) {
        exit('<span class="success">Your album was deleted</span>');
    }
    // retrieve data
    $aid = $arow['id'];
    $atitle = htmlentities($arow['title']);
    $adesc = htmlentities($arow['description']);
    $acover = $arow['cover'];
    $visibility = $arow['visibility'] ? 1 : 0;

    $psel = dbQuery($psel, $aid, $uid);
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
    ?>
    <!--Album-->
    <div class="album" id="<?= $aid ?>">
        <div class="feedback" id="albumFeedback"></div>
        <!--Album cover-->
        <img class="album" src="<?= $acover ?>" alt="<?= $adesc ?>" title="Album cover"/>
        <!--Edit album form-->
        <form id="albumEdit" name="albumEdit" method="POST" action="ajax/ajaxEdit.php">
            <label for="albumDesc">Description:</label><br/>
            <textarea id="albumDesc" rows="4" placeholder="Album description"><?= $adesc ?></textarea><br/>
            <label for="title">Title</label>
            <input type="text" id="title" required="required" maxlength="32" placeholder="Album title" pattern="^.*\S.*$" autocomplete="off" value="<?= $atitle ?>"/>
            <input type="radio" id="public" name="visibility" <?= $visibility ? ' checked="checked"' : '' ?>/>
            <label for="public">Public</label>
            <input type="radio" id="private" name="visibility" <?= !$visibility ? ' checked="checked"' : '' ?>/>
            <label for="private">Private</label><br/>
            <input id="deleteAlbum" type="button" value="Delete"/>
            <input id="saveAlbum" type="submit" value="Save"/>
        </form>
        <h2 id="count"><?= "$showing$count photos" ?></h2><hr/>
        <?= paginate($target, $current, $pages) ?><br/>
        <?php
        // display photos
        foreach ($presult as $prow) {
            // retrieve data
            $pid = $prow['id'];
            $src = $prow['filename'];
            $pdesc = htmlentities($prow['description']);
            $pdate = htmlentities($prow['date']);
            $pcat = htmlentities($prow['category']);
            // display
            echo "<img id=\"$pid\" class=\"photo editable\" src=\"$src\" alt=\"$pdesc\" title=\"$pdate:[$pcat]: $pdesc\" date=\"$pdate\" category=\"$pcat\" description=\"$pdesc\"/>";
        }

        echo '</div>', paginate($target, $current, $pages);
    }

    // show albums by user ID
//    function showAlbums($uid) {
//        $asel = "SELECT id, title, description, cover
//              FROM albums
//              WHERE author_id = ?
//              ORDER BY title";
//
//        $asel = dbQuery($asel, $uid);
//        $aresult = $asel->fetchAll();
//        $count = count($aresult);
//
//        echo "<h1>$count Albums</h1>";
//        // display all albums
//        foreach ($aresult as $arow) {
//            // retrieve data
//            $aid = $arow['id'];
//            $atitle = htmlentities($arow['title']);
//            $adesc = htmlentities($arow['description']);
//            $acover = @is_file($arow['cover']) ? $arow['cover'] : 'images/folder.png';
//
//            echo "<a class=\"photo\" href=\"edit.php?a=$aid\">
//                <img class=\"photo\"
//                    src=\"$acover\"
//                    alt=\"$adesc\"
//                    title=\"$atitle\" /></a>";
//        }
//    }
    // show albums by user ID
    function showAlbums($uid) {
        // SELECT query
        $anum = "SELECT COUNT(id) AS num FROM albums WHERE author_id=?";
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
        $asel = "SELECT id, title, description, cover FROM albums WHERE author_id=? ORDER BY title COLLATE utf8_unicode_ci LIMIT $start, $perPage";
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

        echo "<h1>[$showing$count albums]</h1>", paginate($target, $current, $pages), '<br/>';
        // display albums
        foreach ($aresult as $arow) {
            // retrieve data
            $aid = $arow['id'];
            $atitle = htmlentities($arow['title']);
            $adesc = htmlentities($arow['description']);
            $adescription = "<br/><textarea rows=\"3\" disabled=\"disabled\">$adesc</textarea>";
            $acover = $arow['cover'];
            // display
            echo "<a href=\"edit.php?a=$aid\"><div class=\"mosaic-block bar2\"><div class=\"mosaic-overlay\">"
            . "<div class=\"details\">$atitle $adescription</div></div><img src=\"$acover\" alt=\"$adesc\"/></div></a>";
        }

        echo '<br/>', paginate($target, $current, $pages);
    }
    ?>