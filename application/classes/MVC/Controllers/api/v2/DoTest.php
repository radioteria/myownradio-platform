<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\Beans\StreamBean;
use MVC\Controller;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {
        $stream[] = StreamBean::findByFilter("SEARCH_BY_ANYTHING", ["hello"]);
        $response->setData(count($stream));
    }

} 