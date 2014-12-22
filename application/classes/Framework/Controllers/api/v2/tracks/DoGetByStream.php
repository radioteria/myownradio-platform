<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:37
 */

namespace Framework\Controllers\api\v2\tracks;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Model\StreamModel;
use REST\Playlist;

class DoGetByStream implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, Playlist $playlist) {

        $id = $get->getParameter("stream_id")->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $color = $get->getParameter("color")->getOrElseNull();

        $stream = StreamModel::getInstance($id);

        $response->setData($playlist->getTracksByStream($stream, $color));

    }
} 