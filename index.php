<?php

/*
 * This is the main page of the web site.
 * Currently it shows the 20 most recently uploaded photos.
 * Still under construction!
 */
session_start();
require_once 'core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/pagination.inc.php';
$_SESSION['location'] = $_SERVER['REQUEST_URI'];
// if the user has the cookie
if (!isset($_SESSION['uid']) && isset($_COOKIE['username'])) {
    header('Location: login.php');
    exit;
}

$css .= 'main.css,lightbox.css,mosaic.css';
$js .= 'jquery-min.js,modal-popup.js,footer.js,lightbox.js,mosaic-plugin-min.js,mosaic.js';
include INCLUDE_PATH . '/header.inc.php';
echo '<style type="text/css">#categories{line-height:2.5em}</style>';
// SELECT query
$psel = "SELECT p.category, COUNT(*) AS count FROM photos AS p, albums AS a WHERE a.visibility=1 AND p.category != '' GROUP BY p.category";
$psel = dbQuery($psel);
$presult = $psel->fetchAll();
$i = 0;
$categories = '<section id="content"><h1>Browse Categories</h1><div id="categories"> | ';
$occurances = array_reduce($presult, function($result, $item) {
            array_push($result, $item['count']);
            return $result;
        }, array());
$max = max($occurances);

foreach ($presult as $prow) {
    $i++;
    $cat = $prow['category'];
    $font = max((2.5 * $prow['count'] / $max), 1) . 'em';
    $categories .= "<a href=\"index.php?c=$cat\" style=\"font-size:$font\">$cat</a> | ";

    if ($i % 7 == 0) {
        $categories .= '<br/> | ';
    }
}

$categories .= '</div><hr/>';
echo $categories;

if (isset($_GET['c'])) {
    browse($_GET['c']);
} else {
    echo '<h1>Recently Added Photos</h1>', mostRecent();
}

echo '</section>';
include INCLUDE_PATH . '/footer.inc.php';

// display most recently uploaded photos
function mostRecent() {
    // SELECT query
    $psel = "SELECT * FROM recent LIMIT 20"; // 20 most recent photos
    // if the database does not support views
    /*
      $psel = "SELECT p.id, p.author_id, p.album_id, p.filename, p.date, p.category, p.description, a.title, u.firstname, u.lastname"
      . " FROM photos AS p, albums AS a, users AS u"
      . " WHERE p.album_id = a.id AND a.visibility=1 AND p.author_id = u.id"
      . " GROUP BY p.album_id, p.author_id, p.category"
      . " ORDER BY p.date DESC, a.title COLLATE utf8_unicode_ci"
      . " LIMIT 20"; // 20 most recent photos
     */
    $psel = dbQuery($psel);
    $presult = $psel->fetchAll();
    // display photos
    foreach ($presult as $prow) {
        // retrieve data
        $thumb = $prow['filename'];
        $src = str_replace('thumbs/', '', $thumb);
        $pdesc = htmlentities($prow['description']);
        $pdescription = ($pdesc !== '') ? "<br/><textarea rows=\"3\" disabled=\"disabled\">$pdesc</textarea>" : '';
        $pdate = htmlentities($prow['date']);
        $pcat = htmlentities($prow['category']);
        $pcategory = ($pcat !== '') ? "[<a href=\"index.php?c=$pcat\">$pcat</a>]<br/>" : '';
        $aid = $prow['album_id'];
        $uid = $prow['author_id'];
        $atitle = htmlentities($prow['title']);
        $ufirst = htmlentities($prow['firstname']);
        $ulast = htmlentities($prow['lastname']);
        // display
        echo "<div class=\"mosaic-block fade\"><div class=\"mosaic-overlay\"><div class=\"details\">"
        . "<a href=\"$src\" rel=\"lightbox[Recent]\" caption=\"[$pcat]:$pdate: $pdesc\">Large</a>"
        . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pdate<br/>$pcategory by "
        . "<a href=\"view.php?u=$uid\">$ufirst $ulast</a> in "
        . "<a href=\"view.php?u=$uid&a=$aid\">$atitle</a>$pdescription</div></div>"
        . "<a href=\"$src\" rel=\"lightbox[Recent.2]\" caption=\"[$pcat]:$pdate: $pdesc\"><img src=\"$thumb\"/></a></div>";
    }
}

// get photos by category
function browse($cat) {
    // SELECT query
    $pnum = "SELECT COUNT(*) AS num FROM photos AS p, albums AS a, users AS u"
            . " WHERE p.album_id = a.id AND a.visibility=1 AND p.author_id = u.id AND p.category=?";
    $pnum = dbQuery($pnum, $cat);
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
    $psel = "SELECT p.id, p.author_id, p.album_id, p.filename, p.date, p.description, a.title, u.firstname, u.lastname"
            . " FROM photos AS p, albums AS a, users AS u"
            . " WHERE p.album_id = a.id AND a.visibility=1 AND p.author_id = u.id AND p.category=?"
            . " ORDER BY p.date DESC, a.title COLLATE utf8_unicode_ci"
            . " LIMIT $start, $perPage";
    $psel = dbQuery($psel, $cat);
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
    echo "<h1>$cat</h1><h3>[$showing$count photos]</h3>", paginate($target, $current, $pages), '<br/>';

    foreach ($presult as $prow) {
        // retrieve data
        $thumb = $prow['filename'];
        $src = str_replace('thumbs/', '', $thumb);
        $pdesc = htmlentities($prow['description']);
        $pdescription = ($pdesc !== '') ? "<br/><textarea rows=\"3\" disabled=\"disabled\">$pdesc</textarea>" : '';
        $pdate = htmlentities($prow['date']);
        $aid = $prow['album_id'];
        $uid = $prow['author_id'];
        $atitle = htmlentities($prow['title']);
        $ufirst = htmlentities($prow['firstname']);
        $ulast = htmlentities($prow['lastname']);
        // display
        echo "<div class=\"mosaic-block fade\"><div class=\"mosaic-overlay\"><div class=\"details\">"
        . "<a href=\"$src\" rel=\"lightbox[$cat]\" caption=\"[$cat]:$pdate: $pdesc\">Large</a>"
        . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pdate<br/>by "
        . "<a href=\"view.php?u=$uid\">$ufirst $ulast</a> in "
        . "<a href=\"view.php?u=$uid&a=$aid\">$atitle</a>$pdescription</div></div>"
        . "<a href=\"$src\" rel=\"lightbox[$cat.2]\" caption=\"[$cat]:$pdate: $pdesc\"><img src=\"$thumb\"/></a></div>";
    }

    echo '<br/>', paginate($target, $current, $pages);
}

?>