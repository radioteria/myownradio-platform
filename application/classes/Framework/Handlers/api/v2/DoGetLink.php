<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.01.15
 * Time: 18:58
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;
use Tools\Folders;

class DoGetLink implements Controller {
    public function doGet(HttpGet $get, Folders $folders, JsonResponse $response, Streams $streams) {
        $id = $get->getRequired("stream_id");
        $stream = $streams->getOneStream($id);
        $response->setData([
            "stream" => $stream
        ]);
    }
} 