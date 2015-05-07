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
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Framework\Services\Notifier;
use Objects\Stream;

class DoBookmark implements Controller {

    public function doPut(HttpGet $get, JsonResponse $response, StreamsModel $streams, Notifier $notif1er) {
        $streamID = $get->getRequired("stream_id");

        $stream = Stream::getByID($streamID)
            ->getOrElseThrow(ControllerException::noStream($streamID));
        $streams->addBookmark($stream);

        $notif1er->notify("mor:channel:bookmarked", $streamID);
    }

    public function doDelete(HttpGet $get, JsonResponse $response, StreamsModel $streams, Notifier $notif1er) {
        $streamID = $get->getRequired("stream_id");

        $stream = Stream::getByID($streamID)
            ->getOrElseThrow(ControllerException::noStream($streamID));
        $streams->deleteBookmark($stream);

        $notif1er->notify("mor:channel:bookmark_removed", $streamID);
    }

} 