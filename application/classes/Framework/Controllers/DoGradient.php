<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;

class DoGradient implements Controller {
    public function doGet() {

        $string = "Hello";

        $hash = hash("sha512", $string);

        echo strlen($hash);

    }
} 