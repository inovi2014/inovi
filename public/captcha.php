<?php
    namespace Thin;

    require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';
    require_once APPLICATION_PATH . DS . 'Bootstrap.php';

    Bootstrap::cli();

    $text       = Inflector::random(9);
    session()->setCaptcha($text);
    $height     = 25;
    $width      = 120;
    $font_size  = 14;

    $im         = imagecreatetruecolor($width, $height);
    $textcolor  = imagecolorallocate($im, 80, 80, 80);
    $bg         = imagecolorallocate($im, 0, 0, 0);
    imagestring($im, $font_size, 5, 5,  $text, $textcolor);
    imagecolortransparent($im, $bg);
    imagefill($im, 0, 0, $bg);

    imagepng($im, null, 9);
    imagedestroy($im);
