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
use Framework\Services\Http\HttpGet;
use REST\Playlist;
use Tools\Optional\Filter;

class DoGetByStream implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist) {

        $id = $get->getOrError("stream_id");
        $color = $get->get("color_id")->filter(Filter::$isNumber);
        $offset = $get->get("offset")->filter(Filter::$isNumber);
        $filter = $get->get("filter")->filter(Filter::$notEmpty);

        $stream = StreamModel::getInstance($id);

        $playlist->getTracksByStream($stream, $color, $filter, $offset);

    }
} 