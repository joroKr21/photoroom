<?php
/*
 * This script implements a search engine and the search page.
 */
session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/stemmer.class.php';
require_once INCLUDE_PATH . '/cleaner.class.php';
require_once INCLUDE_PATH . '/pagination.inc.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
// if the user has the cookie
if (!isset($_SESSION['uid']) && isset($_COOKIE['username'])) {
    header('Location: login.php');
    exit;
}
// minimum word length for searching
define('MIN_WORD_LEN', 3);
$css .= 'main.css,lightbox.css,mosaic.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,lightbox.js,mosaic-plugin-min.js,mosaic.js';
$term = isset($_GET['k']) ? trim($_GET['k']) : '';
include INCLUDE_PATH . '/header.inc.php';
?>
<style type="text/css">#search{color:#8ad459}#searchForm{text-align:center;width:80%}#keywords{width:100%}</style>
<!--Main content-->
<section id="content">
    <h1>Search</h1>
    <!--Search form-->
    <form id="searchForm" name="searchForm" method="GET" action="search.php">
        <input type="text" id="keywords" name="k" placeholder="What are you looking for?" pattern="^.*\S.*$" required="required" autocomplete="on"
               value="<?= isset($_GET['k']) ? htmlentities($_GET['k']) : ''; ?>"
               <?= isset($_GET['k']) ? '' : 'autofocus="autofocus"'; ?>/><br/>
        <input type="radio" id="users" name="s" value="users"
               <?= (isset($_GET['s']) and $_GET['s'] == 'users') ? 'checked="checked"' : ''; ?>/>
        <label for="users">Users</label>
        <input type="radio" id="albums" name="s" value="albums"
               <?= (isset($_GET['s']) and $_GET['s'] == 'albums') ? 'checked="checked"' : ''; ?>/>
        <label for="albums">Albums</label>
        <input type="radio" id="photos" name="s" value="photos"
               <?= (isset($_GET['s']) and $_GET['s'] == 'photos') ? 'checked="checked"' : ''; ?>
               <?= isset($_GET['s']) ? '' : 'checked="checked"'; ?>/>
        <label for="photos">Photos</label>
        <input type="submit" name="search" value="Search"/>
    </form>
    <?php
    // check what we are searching for
    if (isset($_GET['search'], $_GET['s'])) {
        if (strlen($term) < 3) {
            echo '<div class="feedback"><span class="error">Please enter more specific key words</span></div>';
        } else {
            switch ($_GET['s']) {
                case 'users': echo searchUsers($term);
                    break;
                case 'albums': echo searchAlbums($term);
                    break;
                case 'photos': echo searchPhotos($term);
                    break;
                default: break;
            }
        }
    }

    echo '</section>';
    include INCLUDE_PATH . '/footer.inc.php';

    // search for users
    function searchUsers($term) {
        // prepare search term
        $keywords = searchPrep($term);
        $term = removeSymbols($term);
        // make sure we have key words
        if (empty($keywords)) {
            return '<div class="feedback"><span class="error">Please enter more specific key words</span></div>';
        }
        // build query
        $unum = "SELECT COUNT(*) AS num ";
        // evaluate score
        $usearch = "SELECT (MATCH(firstname, lastname, email) AGAINST('$term' IN BOOLEAN MODE)) AS score, id, firstname, lastname, picture ";
        $query = "FROM users WHERE (";
        // find all relevant content
        foreach ($keywords as $key) {
            $query .= "(email COLLATE utf8_unicode_ci LIKE $key"
                    . " OR firstname COLLATE utf8_unicode_ci LIKE $key"
                    . " OR lastname COLLATE utf8_unicode_ci LIKE $key) OR ";
        }
        // remove last OR
        $query = substr($query, 0, strlen($query) - 4);
        $unum .= $query . ")";
        $unum = dbQuery($unum);
        $uresult = $unum->fetch();
        // paginate
        $count = $uresult['num'];
        $pages = ceil($count / PERPAGE);
        $current = (isset($_GET['page']) and $_GET['page'] > 0 and $_GET['page'] <= $pages) ? $_GET['page'] : 1;
        $start = ($current - 1) * PERPAGE;
        $perPage = PERPAGE;

        if (isset($_GET['page'])) {
            $target = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - strlen($_GET['page']) - 6);
        } else {
            $target = $_SERVER['REQUEST_URI'];
        }
        // order results
        $usearch .= $query . ") ORDER BY score DESC, username COLLATE utf8_unicode_ci LIMIT $start, $perPage";
        $usearch = dbQuery($usearch);
        $uresult = $usearch->fetchAll();
        // make sure we have results
        if (!$count) {
            return '<div class="feedback"><span class="error">No results found</span></div>';
        }
        // paginate
        if ($pages > 1) {
            $from = $start + 1;
            $to = $start + PERPAGE;
            $to = ($to > $count) ? $count : $to;
            $showing = "$from-$to of ";
        } else {
            $showing = '';
        }

        $message = "<div class=\"feedback\">$showing$count results</div><hr/>" . paginate($target, $current, $pages) . '<br/>';
        // display results
        foreach ($uresult as $urow) {
            // retrieve data
            $uid = $urow['id'];
            $ufirst = htmlentities($urow['firstname']);
            $ulast = htmlentities($urow['lastname']);
            $upic = $urow['picture'];
            $score = htmlentities($urow['score']);
            // display
            $message .= "<div class=\"mosaic-block bar3\"><div class=\"mosaic-overlay\"><div class=\"details\">"
                    . "<a href=\"view.php?u=$uid\">$ufirst $ulast</a><br/><img src=\"ajax/emailImg.php?uid=$uid\" alt=\"email\" style=\"width:180px\"/>"
                    . (DEBUG ? "<br/>$score" : "") . "</div></div><a href=\"view.php?u=$uid\"><img src=\"$upic\" alt=\"$ufirst $ulast\"/></a></div>";
        }

        $message .= '<br/>' . paginate($target, $current, $pages);
        return $message;
    }

    // search for albums
    function searchAlbums($term) {
        // prepare search term
        $keywords = searchPrep($term);
        $term = removeSymbols($term);
        // make sure we have key words
        if (empty($keywords)) {
            return '<div class="feedback"><span class="error">Please enter more specific key words</span></div>';
        }
        // build query
        $anum = "SELECT COUNT(*) AS num ";
        // evaluate score
        $asearch = "SELECT (MATCH(a.title, a.description) AGAINST('$term' IN BOOLEAN MODE) * 3"
                . " + MATCH(u.email, u.firstname, u.lastname) AGAINST('$term' IN BOOLEAN MODE)) AS score,"
                . " a.id, a.author_id, a.title, a.description, a.cover, u.firstname, u.lastname ";
        $query = "FROM albums AS a, users AS u WHERE a.visibility = 1 AND a.author_id = u.id AND (";
        // find all relevant content
        foreach ($keywords as $key) {
            $query .= "(a.title COLLATE utf8_unicode_ci LIKE $key"
                    . " OR a.description COLLATE utf8_unicode_ci LIKE $key"
                    . " OR u.email COLLATE utf8_unicode_ci LIKE $key"
                    . " OR u.firstname COLLATE utf8_unicode_ci LIKE $key"
                    . " OR u.lastname COLLATE utf8_unicode_ci LIKE $key) OR ";
        }
        // remove last OR
        $query = substr($query, 0, strlen($query) - 4);
        $anum .= $query . ")";
        $anum = dbQuery($anum);
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
        // order results
        $asearch .= $query . ") ORDER BY score DESC, a.title COLLATE utf8_unicode_ci LIMIT $start, $perPage";
        $asearch = dbQuery($asearch);
        $aresult = $asearch->fetchAll();
        // make sure we have results
        if (!$count) {
            return '<div class="feedback"><span class="error">No results found</span></div>';
        }
        // paginate
        if ($pages > 1) {
            $from = $start + 1;
            $to = $start + PERPAGE;
            $to = ($to > $count) ? $count : $to;
            $showing = "$from-$to of ";
        } else {
            $showing = '';
        }

        $message = "<div class=\"feedback\">$showing$count results</div><hr/>" . paginate($target, $current, $pages) . '<br/>';
        // display results
        foreach ($aresult as $arow) {
            // retrieve data
            $aid = $arow['id'];
            $uid = $arow['author_id'];
            $atitle = htmlentities($arow['title']);
            $adesc = htmlentities($arow['description']);
            $adescription = ($adesc !== '') ? "<textarea rows=\"3\" disabled=\"disabled\">$adesc</textarea><br/>" : '';
            $acover = $arow['cover'];
            $ufirst = htmlentities($arow['firstname']);
            $ulast = htmlentities($arow['lastname']);
            $score = htmlentities($arow['score']);
            // display
            $message .= "<div class=\"mosaic-block fade\"><div class=\"mosaic-overlay\"><div class=\"details\">"
                    . "<a href=\"view.php?u=$uid&a=$aid\">$atitle</a><br/>$adescription by <a href=\"view.php?u=$uid\">$ufirst $ulast</a>"
                    . (DEBUG ? "<br/>$score" : "") . "</div></div><a href=\"view.php?u=$uid&a=$aid\"><img src=\"$acover\" alt=\"$adesc\"/></a></div>";
        }

        $message .= '<br/>' . paginate($target, $current, $pages);
        return $message;
    }

    // search for photos
    function searchPhotos($term) {
        // prepare search term
        $keywords = searchPrep($term);
        $term = removeSymbols($term);
        // make sure we have key words
        if (empty($keywords)) {
            return '<div class="feedback"><span class="error">Please enter more specific key words</span></div>';
        }
        // build query
        $pnum = "SELECT COUNT(*) AS num ";
        // evauate score
        $psearch = "SELECT (MATCH(p.category, p.description) AGAINST('$term' IN BOOLEAN MODE) * 3"
                . " + MATCH(a.title, a.description) AGAINST('$term' IN BOOLEAN MODE)"
                . " + MATCH(u.email, u.firstname, u.lastname) AGAINST('$term' IN BOOLEAN MODE)) AS score,"
                . " p.id, p.author_id, p.album_id, p.filename, p.date, p.category, p.description, a.title, u.firstname, u.lastname ";
        $query = "FROM photos AS p, albums AS a, users AS u WHERE p.album_id = a.id AND a.visibility = 1 AND p.author_id = u.id AND (";
        // find all relevant content
        foreach ($keywords as $key) {
            $query .= "(p.category COLLATE utf8_unicode_ci LIKE $key"
                    . " OR p.description COLLATE utf8_unicode_ci LIKE $key"
                    . " OR a.title COLLATE utf8_unicode_ci LIKE $key"
                    . " OR a.description COLLATE utf8_unicode_ci LIKE $key"
                    . " OR u.email COLLATE utf8_unicode_ci LIKE $key"
                    . " OR u.firstname COLLATE utf8_unicode_ci LIKE $key"
                    . " OR u.lastname COLLATE utf8_unicode_ci LIKE $key) OR ";
        }
        // remove last OR
        $query = substr($query, 0, strlen($query) - 4);
        $pnum .= $query . ")";
        $pnum = dbQuery($pnum);
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
        // order results
        $psearch .= $query . ") ORDER BY score DESC, p.date DESC, a.title COLLATE utf8_unicode_ci LIMIT $start, $perPage";
        $psearch = dbQuery($psearch);
        $presult = $psearch->fetchAll();
        // make sure we have results
        if (!$count) {
            return '<div class="feedback"><span class="error">No results found</span></div>';
        }
        // paginate
        if ($pages > 1) {
            $from = $start + 1;
            $to = $start + PERPAGE;
            $to = ($to > $count) ? $count : $to;
            $showing = "$from-$to of ";
        } else {
            $showing = '';
        }

        $message = "<div class=\"feedback\">$showing$count results</div><hr/>" . paginate($target, $current, $pages) . '<br/>';
        // display results
        foreach ($presult as $prow) {
            // retrieve data
            $uid = $prow['author_id'];
            $aid = $prow['album_id'];
            $thumb = $prow['filename'];
            $src = str_replace('thumbs/', '', $thumb);
            $pdate = htmlentities($prow['date']);
            $pcat = htmlentities($prow['category']);
            $pcategory = ($pcat !== '') ? "[<a href=\"index.php?c=$pcat\">$pcat</a>]<br/>" : '';
            $pdesc = htmlentities($prow['description']);
            $pdescription = ($pdesc !== '') ? "<br/><textarea rows=\"3\" disabled=\"disabled\">$pdesc</textarea>" : '';
            $atitle = htmlentities($prow['title']);
            $ufirst = htmlentities($prow['firstname']);
            $ulast = htmlentities($prow['lastname']);
            $score = htmlentities($prow['score']);
            // display
            $message .= "<div class=\"mosaic-block fade\"><div class=\"mosaic-overlay\"><div class=\"details\">"
                    . "<a href=\"$src\" rel=\"lightbox[Search]\" caption=\"[$pcat]:$pdate: $pdesc\">Large</a>"
                    . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pdate<br/>$pcategory by "
                    . "<a href=\"view.php?u=$uid\">$ufirst $ulast</a> in <a href=\"view.php?u=$uid&a=$aid\">$atitle</a>$pdescription"
                    . (DEBUG ? "<br/>$score" : "") . "</div></div>"
                    . "<a href=\"$src\" rel=\"lightbox[Search.2]\" caption=\"[$pcat]:$pdate: $pdesc\"><img src=\"$thumb\" alt=\"$pdesc\"/></a></div>";
        }

        $message .= '<br/>' . paginate($target, $current, $pages);
        return $message;
    }

    // prepare a term for searching
    function searchPrep($string) {
        $stemmer = new Stemmer();
        $cleaner = new Cleaner();
        $result = $cleaner->cleanString($string);
        $split = explode(' ', $result);
        $result = '';
        // stem and escape the words
        foreach ($split as $value) {
            $value = $stemmer->stem($value);

            if (strlen($value) < MIN_WORD_LEN) {
                continue;
            } else {
                $result .= dbEscape("%$value%") . ' ';
            }
        }

        $result = trim($result);
        // check if we have a search term
        if ($result !== '') {
            $split = explode(' ', $result);
            return $split;
        } else {
            return false;
        }
    }

    // remove punctuation from a search term
    function removeSymbols($string) {
        $cleaner = new Cleaner();
        return $cleaner->removeSymbols($string);
    }
    ?>