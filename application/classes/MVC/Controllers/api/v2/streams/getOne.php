<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:53
 */

namespace MVC\Controllers\api\v2\streams;

use MVC\Controller;
use MVC\Services\HttpGet;
use MVC\Services\HttpPost;

class getOne extends Controller {
    public function doGet(HttpGet $get, HttpPost $post) {
        echo $get->getParameter("id")->getOrElse("no id");
    }
} 