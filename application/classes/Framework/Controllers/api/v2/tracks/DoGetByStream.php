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
use Framework\Models\StreamModel;
use Framework\Services\HttpGet;
use REST\Playlist;

class DoGetByStream implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist) {

        $id = $get->getParameter("stream_id")->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $color = $get->getParameter("color_id")->getOrElseNull();

        $stream = StreamModel::getInstance($id);

        $playlist->getTracksByStream($stream, $color);

    }
} 