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
use Framework\Services\JsonResponse;
use REST\Playlist;

class DoGetAll implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, Playlist $playlist) {
        $color = $get->getParameter("color_id")->getOrElseNull();
        $tracks = $playlist->getAllTracks($color);
        $response->setData($tracks);
    }
} 