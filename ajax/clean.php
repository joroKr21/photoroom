<?php

/*
 * This script is used for maintenance by administrators to clean up any trash
 * files in the photo storage folder on the server it is executed
 */
session_start();
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';
require_once INCLUDE_PATH . '/rrmdir.inc.php';
// allow only administrators to execute this script
if ((!isset($_SESSION['uid']) || $_SESSION['level'] <= 0)) {
    exit('<span class="error">You do not have permission to execute this script</span>');
} else {
    cleanUsers(STORE_PATH);
    exit('<span class="success">Clean completed successfully</span>');
}

// clean user folders in the photo storage directory
function cleanUsers($dir) {
    // SELECT query
    $usel = "SELECT id FROM users";
    $usel = dbQuery($usel);
    $uresult = $usel->fetchAll();
    // fold to array of user IDs
    $uresult = array_reduce($uresult, function($result, $item) {
                array_push($result, $item['id']);
                return $result;
            }, array());
    // parse folders in photo storage directory
    foreach (glob("$dir/*") as $dirPath) {
        if (is_dir($dirPath)) {
            $dirName = array_pop(explode('/', $dirPath));
            if (!in_array($dirName, $uresult)) {
                rrmdir($dirPath);
            } else {
                cleanAlbums($dirPath);
            }
        }
    }
}

// clean albums in user directory
function cleanAlbums($dir) {
    $uid = array_pop(explode('/', $dir));
    // SELECT query
    $asel = "SELECT id FROM albums WHERE author_id=?";
    $asel = dbQuery($asel, $uid);
    $aresult = $asel->fetchAll();
    // fold to array of album IDs
    $aresult = array_reduce($aresult, function($result, $item) {
                array_push($result, $item['id']);
                return $result;
            }, array());
    // parse folders in user directory
    foreach (glob("$dir/*") as $dirPath) {
        $dirName = array_pop(explode('/', $dirPath));
        if (is_dir($dirPath)) {
            if (!in_array($dirName, $aresult)) {
                rrmdir($dirPath);
            } else {
                cleanPhotos("$dirPath/thumbs");
            }
        }
    }
}

// clean photos in album directory
function cleanPhotos($dir) {
    $arr = explode('/', $dir);
    $aid = $arr[count($arr) - 2];
    // SELECT query
    $psel = "SELECT filename FROM photos WHERE album_id=?";
    $psel = dbQuery($psel, $aid);
    $presult = $psel->fetchAll();
    // fold to array of user IDs
    $presult = array_reduce($presult, function($result, $item) {
                array_push($result, array_pop(explode('/', $item['filename'])));
                return $result;
            }, array());
    // parse folders in photo storage directory
    foreach (glob("$dir/*") as $file) {
        $fname = array_pop(explode('/', $file));
        if (is_file($file)) {
            if (!in_array($fname, $presult)) {
                unlink($file);
                unlink("$dir/../$fname");
            }
        }
    }
}

?>