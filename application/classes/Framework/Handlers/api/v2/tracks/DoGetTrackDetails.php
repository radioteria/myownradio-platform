<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.01.15
 * Time: 12:02
 */

namespace Framework\Handlers\api\v2\tracks;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Playlist;

class DoGetTrackDetails implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, Playlist $playlist) {
        $track_id = $get->getRequired("track_id");
        $response->setData($playlist->getOneTrack($track_id));
    }
} 