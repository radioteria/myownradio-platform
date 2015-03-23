<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 23.03.15
 * Time: 9:49
 */

namespace Framework\Controllers\pages;


use Framework\Controller;

class DoIndex implements Controller {
    public function doGet() {
        echo "OK";
    }
} 