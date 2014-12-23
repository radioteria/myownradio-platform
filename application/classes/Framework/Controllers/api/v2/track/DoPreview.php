<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 21:32
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Framework\Services\Services;

class DoPreview implements Controller {

    public function doGet(HttpGet $get, Services $services) {

        $trackID = $get->getParameter("track_id")->getOrElseThrow(ControllerException::noArgument("track_id"));

        $services->getTrackModel($trackID)->preview();

    }

} 