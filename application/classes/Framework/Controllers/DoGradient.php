<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\HttpGet;

class DoGradient implements Controller {
    public function doGet(HttpGet $get) {

        header("Content-type: image/png");

        $width = 250;
        $height = 250;

        $angle = 45;

        $start = $get->getParameter("start")->getOrElse('252556');
        $end = $get->getParameter("end")->getOrElse('381F49');

        $start_r = hexdec(substr($start, 0, 2));
        $start_g = hexdec(substr($start, 2, 2));
        $start_b = hexdec(substr($start, 4, 2));
        $end_r = hexdec(substr($end, 0, 2));
        $end_g = hexdec(substr($end, 2, 2));
        $end_b = hexdec(substr($end, 4, 2));
        $image = @imagecreate($width, $height);
        $x_rate = $height / 100 * $angle;
        $y_rate = $width / 100 * $angle;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($start_r == $end_r) {
                    $new_r = $start_r;
                }
                $difference = $start_r - $end_r;
                $new_r = $start_r - intval(($difference / $height) * $y);
                if ($start_g == $end_g) {
                    $new_g = $start_g;
                }
                $difference = $start_g - $end_g;
                $new_g = $start_g - intval(($difference / $height) * $y);
                if ($start_b == $end_b) {
                    $new_b = $start_b;
                }
                $difference = $start_b - $end_b;
                $new_b = $start_b - intval(($difference / $height) * $y);
                $row_color = imagecolorresolve($image, $new_r, $new_g, $new_b);
                imagesetpixel($image, $x, $y, $row_color);


            }
        }

        $rotate = imagerotate($image, $angle, 0) ;

        error_log(imagesx($rotate));
        error_log($width);

        imagepng($rotate);
        imagedestroy($rotate);

    }
} 