<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:56
 */

namespace Framework\Controllers\api\v2\tracks;


use Framework\Controller;
use Framework\Services\HttpGet;
use REST\Playlist;

class DoGetAll implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist) {

        $color = $get->getParameter("color_id");
        $offset = $get->getParameter("offset");
        $filter = $get->getParameter("filter");
        $unused = boolval($get->getParameter("unused")->getOrElseFalse());

        $sortRow = $get->getParameter("row")->getOrElse(0);
        $sortOrder = $get->getParameter("order")->getOrElse(0);

        if ($unused) {
            $playlist->getUnusedTracks($color, $filter, $offset, $sortRow, $sortOrder);
        } else {
            $playlist->getAllTracks($color, $filter, $offset, $sortRow, $sortOrder);
        }

    }
} 