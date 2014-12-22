<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:56
 */

namespace Framework\Controllers\api\v2\tracks;


use Framework\Controller;
use Framework\Services\JsonResponse;
use REST\Playlist;

class DoGetAll implements Controller {
    public function doGet(JsonResponse $response, Playlist $playlist) {
        $tracks = $playlist->getAllTracks();
        $response->setData($tracks);
    }
} 