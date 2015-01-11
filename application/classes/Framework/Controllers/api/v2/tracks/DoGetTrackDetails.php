<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.01.15
 * Time: 12:02
 */

namespace Framework\Controllers\api\v2\tracks;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Objects\Track;

class DoGetTrackDetails implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response) {
        $track_id = $get->getRequired("track_id");
        $response->setData(Track::getByID($track_id)->getOrElseThrow(ControllerException::noTrack($track_id)));
    }
} 