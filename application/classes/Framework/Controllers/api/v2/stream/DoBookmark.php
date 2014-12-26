<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.12.14
 * Time: 11:47
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\StreamModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoBookmark implements Controller {

    public function doPut(HttpPost $post, JsonResponse $response) {
        $streamID = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));

        $stream = StreamModel::getInstance($streamID);
        $stream->addBookmark();
    }

    public function doDelete(HttpPost $post, JsonResponse $response) {
        $streamID = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));

        $stream = StreamModel::getInstance($streamID);
        $stream->deleteBookmark();

    }

} 