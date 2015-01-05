<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.01.15
 * Time: 18:58
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Objects\Stream;
use Tools\Folders;

class DoGetLink implements Controller {
    public function doGet(HttpGet $get, Folders $folders, JsonResponse $response) {
        $id = $get->getRequired("stream_id");
        $stream = Stream::getByID($id)->getOrElseThrow(ControllerException::noStream($id));
        /** @var Stream $stream */
        $response->setData([
            "url" => $stream->getStreamUrl(),
            "stream" => $stream
        ]);
    }
} 