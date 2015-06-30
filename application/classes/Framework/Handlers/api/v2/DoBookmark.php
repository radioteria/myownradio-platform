<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.12.14
 * Time: 11:47
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\StreamsModel;
use Framework\Services\JsonResponse;
use Objects\Stream;

class DoBookmark implements Controller {

    public function doPut($stream_id, JsonResponse $response, StreamsModel $streams) {

        $stream = Stream::getByID($stream_id)
            ->getOrThrow(ControllerException::noStream($stream_id));

        $streams->addBookmark($stream);

    }

    public function doDelete($stream_id, JsonResponse $response, StreamsModel $streams) {

        $stream = Stream::getByID($stream_id)
            ->getOrThrow(ControllerException::noStream($stream_id));

        $streams->deleteBookmark($stream);

    }

} 