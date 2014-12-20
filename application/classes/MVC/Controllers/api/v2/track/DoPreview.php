<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 21:32
 */

namespace MVC\Controllers\api\v2\track;


use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpGet;
use MVC\Services\JsonResponse;
use MVC\Services\Services;

class DoPreview extends Controller {

    public function doGet(HttpGet $get, Services $services, JsonResponse $response) {

        $trackID = $get->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        $response->disable();

        $services->getTrackModel($trackID)->preview();

    }

} 