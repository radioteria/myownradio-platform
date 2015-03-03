<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 03.03.15
 * Time: 12:08
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Objects\Track;

class DoTag implements Controller {
    public function doGet() {
        $a = "123";
        echo isset($a) ?  "true" : "false";
    }
} 