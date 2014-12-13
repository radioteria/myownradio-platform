<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 23:32
 */

namespace MVC\Controllers\api\v2\streams;


use MVC\Controller;
use MVC\Services\HttpResponse;

class testUnit extends Controller {
    public function doGet(HttpResponse $response) {
        $response->setMessage("OK");
    }
} 