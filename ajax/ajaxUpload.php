<?php

/*
 * This script handles the server side for image uploading with ajax.
 */
session_start();
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/album.inc.php';
require_once INCLUDE_PATH . '/photo.inc.php';
// only registered users can upload photos
if (isset($_SESSION['uid']) && isset($_POST['album'])) {
    // album not found and could not be created
    if (!$aid = getAlbum($_SESSION['uid'], $_POST['album'])) {
        exit('<span class="error">An error occured</span>');
    } else { // upload photo
        echo upPhoto($_SESSION['uid'], $aid, $_FILES['photo'], $_POST['category']);
    }
}
?>