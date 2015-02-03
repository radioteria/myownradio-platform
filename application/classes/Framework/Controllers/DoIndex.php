<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 29.12.14
 * Time: 16:59
 */

namespace Framework\Controllers;


use Framework\Controller;
use Objects\Track;

class DoIndex implements Controller {
    public function doGet() {
        echo count(Track::getList());
    }
} 