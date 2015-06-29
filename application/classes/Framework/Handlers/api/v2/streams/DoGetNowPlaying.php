<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 08.01.15
 * Time: 14:49
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use Framework\Services\JsonResponse;
use REST\Playlist;

class DoGetNowPlaying implements Controller {
    public function doGet($stream_id, Playlist $playlist, JsonResponse $response) {
        return $playlist->getNowPlaying($stream_id);
    }
} 