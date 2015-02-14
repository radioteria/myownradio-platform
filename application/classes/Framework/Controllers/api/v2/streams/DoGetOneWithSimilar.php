<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.02.15
 * Time: 12:37
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetOneWithSimilar implements Controller {

    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {

        $id = $get->getRequired("stream_id");

        $one = $streams->getOneStream($id);
        $similar = $streams->getSimilarTo($id);

        $response->setData([
            "stream"  => $one,
            "similar" => $similar
        ]);

    }

} 