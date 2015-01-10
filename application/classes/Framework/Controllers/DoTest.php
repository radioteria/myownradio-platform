<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 10:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\HttpGet;

class DoTest implements Controller {
    public function doGet(HttpGet $get) {
        $id = $get->getRequired("id");
        echo "<h1>id = " . $id . "</h1>";
    }
} 