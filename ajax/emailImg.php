<?php

/*
 * This script converts an email string into an image to protect against spam.
 */
require_once '../core.inc.php';
require_once DB_PATH . '/db.php';

if (isset($_GET['uid'])) {
    header('Content-Type: image/png');
    // SELECT query
    $usel = "SELECT email
            FROM users
            WHERE id=?
            LIMIT 1"; // LIMIT 1 is for optimization

    $usel = dbQuery($usel, $_GET['uid']);
    $urow = $usel->fetch();

    if (empty($urow)) {
        exit;
    }
    // create the email image
    $email = $urow['email'];
    $font_size = 10;
    $img_width = imagefontwidth($font_size) * strlen($email);
    $img_height = imagefontheight($font_size);
    $img = imagecreate($img_width, $img_height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    imagecolortransparent($img, imagecolorallocate($img, 255, 255, 255));
    $font_color = imagecolorallocate($img, 204, 51, 0);
    imagestring($img, $font_size, 0, 0, $email, $font_color);
    // output the email image
    imagepng($img);
    imagedestroy($img);
}
?>
