<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:56
 */

namespace Framework\Handlers\api\v2\tracks;


use Framework\Controller;
use Framework\Services\Http\HttpGet;
use REST\Playlist;
use Tools\Optional\Filter;
use Tools\Optional\Transform;

class DoGetAll implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist) {

        $color = $get->get("color_id")->filter(Filter::isNumber());
        $offset = $get->get("offset")->filter(Filter::isNumber());
        $filter = $get->get("filter")->filter(Filter::isNumber());
        $unused = $get->get("unused")->map(Transform::toBoolean())->orFalse();

        $sortRow    = $get->get("row")->filter(Filter::isNumber())->orZero();
        $sortOrder  = $get->get("order")->filter(Filter::isNumber())->orZero();

        if ($unused == true) {
            $playlist->getUnusedTracks($color, $filter, $offset, $sortRow, $sortOrder);
        } else {
            $playlist->getAllTracks($color, $filter, $offset, $sortRow, $sortOrder);
        }

    }
} 