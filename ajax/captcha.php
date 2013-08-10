<?php

/*
 * This script creates a captcha image.
 */
session_start();
require_once '../core.inc.php';
header('Content-Type: image/png');
// determine variables
$_SESSION['captcha'] = substr(md5(rand()), 0, rand(4, 8));
$text = str_split($_SESSION['captcha']);
$fonts = glob(FONTS_PATH . '/*.ttf');
$font_size = 30;
$img_width = 200;
$img_height = 60;
$img = imagecreate($img_width, $img_height);
imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
$left = 20;
$top = 45;
// draw random lines
for ($x = 0; $x < 30; $x++) {
    $x1 = rand(0, $img_width);
    $y1 = rand(0, $img_height);
    $x2 = rand(0, $img_width);
    $y2 = rand(0, $img_height);
    $line_color = imagecolorallocate($img, rand(0, 200), rand(0, 200), rand(0, 200));
    imageline($img, $x1, $y1, $x2, $y2, $line_color);
}
// draw random human readable text
foreach ($text as $char) {
    $text_font = $fonts[rand(0, count($fonts) - 1)];
    $color = imagecolorallocate($img, rand(50, 200), rand(50, 200), rand(50, 200));
    $text_color = $color ? $color : $black;
    imagettftext($img, $font_size, 0, $left, $top, $text_color, $text_font, $char);
    $left += 20;
}
// output the captcha image
imagepng($img);
imagedestroy($img);
?>
