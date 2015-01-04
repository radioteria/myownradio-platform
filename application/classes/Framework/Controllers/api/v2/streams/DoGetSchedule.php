<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:57
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Playlist;

class DoGetSchedule implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist, JsonResponse $response) {
        $id = $get->getRequired("stream_id");
        $response->setData($playlist->getNowPlaying($id));
    }
} 