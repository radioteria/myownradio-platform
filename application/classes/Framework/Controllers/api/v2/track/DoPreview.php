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

class DoPreview extends Controller {

    public function doGet(HttpGet $get, Services $services, JsonResponse $response) {

        $trackID = $get->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        $response->disable();

        $services->getTrackModel($trackID)->preview();

    }

} 