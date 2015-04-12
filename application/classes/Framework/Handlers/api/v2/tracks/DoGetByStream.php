<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:37
 */

namespace Framework\Handlers\api\v2\tracks;


use Framework\Controller;
use Framework\Models\StreamModel;
use Framework\Services\HttpGet;
use REST\Playlist;

class DoGetByStream implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist) {

        $id = $get->getRequired("stream_id");
        $color = $get->getParameter("color_id");
        $offset = $get->getParameter("offset");
        $filter = $get->getParameter("filter");

        $stream = StreamModel::getInstance($id);

        $playlist->getTracksByStream($stream, $color, $filter, $offset);

    }
} 