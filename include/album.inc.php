<?php

/*
 * This script contains functions for album editing.
 * NOTE: must include db.php and rrmdir.inc.php
 */

// edit album
function updateAlbum($aid, $atitle, $adesc, $visibility) {
    // trim input
    $atitle = trim($atitle);
    $adesc = trim($adesc);
    $visibility = $visibility ? 1 : 0;
    // if the title is valid
    if ($atitle !== '') {
        // UPDATE query
        $aupd = "UPDATE albums SET title=?, description=?, visibility=? WHERE id=? LIMIT 1";
        dbQuery($aupd, $atitle, $adesc, $visibility, $aid);
        return '<span class="success">Your changes have been saved</span>';
    } else {
        return '<span class="error">Invalid album title</span>';
    }
}

// delete album
function deleteAlbum($aid, $uid) {
    // delete all files, associated with this album
    if (is_dir(STORE_PATH . "/$uid/$aid")) {
        rrmdir(STORE_PATH . "/$uid/$aid");
    }
    // DELETE query
    $adel = "DELETE FROM albums WHERE id=? LIMIT 1";
    // DELETE query
    $pdel = "DELETE FROM photos WHERE album_id=?";
    dbQuery($pdel, $aid);
    dbQuery($adel, $aid);
}

// either get existing album by user ID and title or create a new one
function getAlbum($uid, $atitle) {
    global $db;
    try {
        // begin database transaction
        $db->beginTransaction();
        // SELECT query
        $asel = "SELECT id FROM albums WHERE author_id=? AND title=? LIMIT 1";
        // INSERT query
        $ains = "INSERT INTO albums (author_id, title, visibility) VALUES(?,?,?)";
        $asel = dbQuery($asel, $uid, $atitle);
        $arow = $asel->fetch();
        // if album exists
        if (!empty($arow)) {
            // end transaction
            $db->commit();
            return $arow['id'];
        } else { // else create a new album
            // validate input
            $atitle = trim($atitle);
            if ($atitle === '') {
                // end database transaction (invalid title)
                $db->commit();
                return false;
            }

            dbQuery($ains, $uid, $atitle, 1);
            $aid = dbInsertID();
            // try creating folders or rollback
            if (is_dir(STORE_PATH . "/$uid/$aid") || mkdir(STORE_PATH . "/$uid/$aid", 0777, true)) {
                if (is_dir(STORE_PATH . "/$uid/$aid/thumbs") || mkdir(STORE_PATH . "/$uid/$aid/thumbs")) {
                    // end database transaction
                    $db->commit();
                    return $aid;
                }
            }
            // rollback database transaction
            $db->rollBack();
            return false;
        }
    } catch (PDOException $e) {
        prettyDie('Database Error: ' . $e->getMessage());
    }
}

?>
