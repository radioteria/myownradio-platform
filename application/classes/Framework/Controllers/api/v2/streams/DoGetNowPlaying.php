<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 08.01.15
 * Time: 14:49
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Playlist;

class DoGetNowPlaying implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist, JsonResponse $response) {
        $id = $get->getRequired("stream_id");
        $response->setData($playlist->getNowPlaying($id));
    }
} 