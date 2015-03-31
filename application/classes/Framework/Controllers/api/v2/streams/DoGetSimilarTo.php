<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:45
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetSimilarTo implements Controller {

    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {
        $id = $get->getRequired("stream_id");
        $response->setData($streams->getSimilarTo($id));
    }

} 