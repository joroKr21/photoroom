<?php

/*
 * This script contains functions for photo editing.
 * NOTE: must include db.php
 */

// edit photo
function updatePhoto($pid, $pcat, $pdesc) {
    // validate input
    $pcat = trim($pcat);
    $pdesc = trim($pdesc);
    // UPDATE query
    $pupd = "UPDATE photos SET category=?, description=? WHERE id=? LIMIT 1";
    dbQuery($pupd, $pcat, $pdesc, $pid);
}

// delete photo
function deletePhoto($pid) {
    // DELETE query
    $pdel = "DELETE FROM photos WHERE id=? LIMIT 1";
    dbQuery($pdel, $pid);
}

// set profile picture
function setProfilePic($uid, $src) {
    $src = str_replace(DOMAIN, $_SERVER['DOCUMENT_ROOT'], $src);
    // copy image
    if (!((is_dir(STORE_PATH . "/$uid") || mkdir(STORE_PATH . "/$uid", 0777, true)) && copy($src, STORE_PATH . "/$uid/folder.jpg"))) {
        exit;
    } else {
        // UPDATE query
        $uupd = "UPDATE users SET picture=? WHERE id=? LIMIT 1";
        dbQuery($uupd, PHOTOS_URL . "/$uid/folder.jpg", $uid);
    }
}

// set album cover
function setAlbumCover($pid, $src) {
    $src = str_replace(DOMAIN, $_SERVER['DOCUMENT_ROOT'], $src);
    // SELECT query
    $psel = "SELECT author_id, album_id FROM photos WHERE id=? LIMIT 1";
    // UPDATE query
    $aupd = "UPDATE albums SET cover=? WHERE id=? LIMIT 1";
    $psel = dbQuery($psel, $pid);
    $prow = $psel->fetch();
    // if the photo exists, set it as album cover
    if (!empty($prow)) {
        $uid = $prow['author_id'];
        $aid = $prow['album_id'];
        // copy image
        if (!((is_dir(STORE_PATH . "/$uid/$aid") || mkdir(STORE_PATH . "/$uid/$aid", 0777, true)) && copy($src, STORE_PATH . "/$uid/$aid/folder.jpg"))) {
            exit;
        } else {
            dbQuery($aupd, PHOTOS_URL . "/$uid/$aid/folder.jpg", $aid);
        }
    }
}

// upload photo
function upPhoto($uid, $aid, $pic, $pcat) {
    $tmp = $pic['tmp_name'];
    // check for upload errors
    if ($pic['error'] == 1 || $pic['error'] == 2) {
        return '<img class="error" src="images/error.png" title="File ' . $pic['name'] . ' too big" alt="File ' . $pic['name'] . ' too big"/>';
    } else if ($pic['error']) {
        return '<img class="error" src="images/error.png" title="Error uploading file ' . $pic['name'] . '" alt="Error uploading file ' . $pic['name'] . '"/>';
    } else { // no errors => check file type and create unique filename
        switch ($pic['type']) {
            case 'image/jpeg':
                $pfname = uniqid('jpg', true) . '.jpg';
                $ptype = 'jpg';
                break;
            case 'image/png':
                $pfname = uniqid('png', true) . '.png';
                $ptype = 'png';
                break;
            case 'image/gif':
                $pfname = uniqid('gif', true) . '.gif';
                $ptype = 'gif';
                break;
            default: // unknown file type
                return '<img class="error" src="images/error.png" title="Unsupported format of file ' . $pic['name'] . '" alt="Unsupported format of file ' . $pic['name'] . '"/>';
        }
    }
    // determine paths
    $src = STORE_PATH . "/$uid/$aid";
    $thumb = "$src/thumbs";
    $url = PHOTOS_URL . "/$uid/$aid/thumbs/$pfname";
    // try to create folders if they do not exist
    if (!(is_dir($src) || mkdir($src, 0777, true)) || !(is_dir($thumb) || mkdir($thumb))) {
        return '<img class="error" src="images/error.png" title="Error uploading file ' . $pic['name'] . '" alt="Error uploading file ' . $pic['name'] . '"/>';
    } else {
        $src .= "/$pfname";
        $thumb .= "/$pfname";
    }
    // try to create thumbnail
    if (!squareCrop($tmp, $thumb, $ptype)) {
        return '<img class="error" src="images/error.png" title="Error uploading file ' . $pic['name'] . '" alt="Error uploading file ' . $pic['name'] . '"/>';
    }
    // try to move image to album folder
    if (!rename($tmp, $src)) {
        unlink($thumb);
        return '<img class="error" src="images/error.png" title="Error uploading file ' . $pic['name'] . '" alt="Error uploading file ' . $pic['name'] . '"/>';
    }
    // INSERT query
    $pins = "INSERT INTO photos (album_id, author_id, filename, category, date) VALUES(?,?,?,?,CURDATE())";
    // validate input
    $pcat = trim($pcat);

    dbQuery($pins, $aid, $uid, $url, $pcat);
    $pid = dbInsertID();
    // validate output
    $pcat = htmlentities($pcat);
    $pdate = date('Y-m-d');

    return "<img class=\"photo\" id=\"$pid\" src=\"$url\" title=\"$pdate:[$pcat]:\" date=\"$pdate\" category=\"$pcat\" description=\"\"/>";
}

// create square thumbnail of an image
function squareCrop($src_image, $dest_image, $img_type, $thumb_size = 200, $jpg_quality = 90) {
    // get dimensions of existing image
    $image = getimagesize($src_image);
    // check for valid dimensions
    if ($image[0] <= 0 or $image[1] <= 0) {
        return false;
    }
    if ($image[0] * $image[1] > 5000 * 5000) {
        return false;
    }
    // import image
    switch ($img_type) {
        case 'jpg':
        case 'jpeg':
            $image_data = imagecreatefromjpeg($src_image);
            break;
        case 'png':
            $image_data = imagecreatefrompng($src_image);
            break;
        case 'gif':
            $image_data = imagecreatefromgif($src_image);
            break;
        default: // unsupported file format
            return false;
            break;
    }
    // verify import
    if ($image_data == false) {
        return false;
    }
    // calculate measurements
    if ($image[0] > $image[1]) { // for landscape images
        $x_offset = ($image[0] - $image[1]) / 2;
        $y_offset = 0;
        $square_size = $image[0] - ($x_offset * 2);
    } else { // for portrait and square images
        $x_offset = 0;
        $y_offset = ($image[1] - $image[0]) / 2;
        $square_size = $image[1] - ($y_offset * 2);
    }
    // resize and crop
    $canvas = imagecreatetruecolor($thumb_size, $thumb_size);
    if (imagecopyresampled($canvas, $image_data, 0, 0, $x_offset, $y_offset, $thumb_size, $thumb_size, $square_size, $square_size)) {
        imagedestroy($image_data);
        // create thumbnail
        switch ($img_type) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($canvas, $dest_image, $jpg_quality);
                break;
            case 'png':
                return imagepng($canvas, $dest_image);
                break;
            case 'gif':
                return imagegif($canvas, $dest_image);
                break;
            default: // unsupported file format
                return false;
                break;
        }
    } else {
        return false;
    }
}

?>
