<?php

/*
 * This script handles the server side for editing photos and albums with ajax.
 */
session_start();
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/album.inc.php';
require_once INCLUDE_PATH . '/photo.inc.php';
require_once INCLUDE_PATH . '/rrmdir.inc.php';
// only registered users can edit albums and photos
if (!isset($_SESSION['uid'])) {
    exit;
} else if (isset($_POST['delPic'])) {
    deletePhoto($_POST['pid']);
    exit('<span class="success">Done</span>');
} else if (isset($_POST['savePic'])) {
    updatePhoto($_POST['pid'], $_POST['category'], $_POST['description']);
    // set profile picture
    if ($_POST['profilePic']) {
        setProfilePic($_SESSION['uid'], $_POST['src']);
    }
    // set album cover
    if ($_POST['albumCover']) {
        setAlbumCover($_POST['pid'], $_POST['src']);
    }

    exit('<span class="success">Done</span>');
} else if (isset($_POST['delAlbum'])) {
    deleteAlbum($_POST['aid'], $_SESSION['uid']);
    exit('<span class="success">Album deleted successfully</span>');
} else if (isset($_POST['saveAlbum'])) {
    echo updateAlbum($_POST['aid'], $_POST['title'], $_POST['description'], $_POST['visibility']);
}
?>