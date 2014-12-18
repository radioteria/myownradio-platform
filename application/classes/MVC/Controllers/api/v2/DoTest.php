<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\MicroORM;
use MVC\Services\Database;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        $orm = new MicroORM();

        $track = $orm->fetchObject("Model\\Beans\\TrackBean", 924);

        $track->setColor(1);

        $res = $orm->saveObject($track);

        $response->setData($res);

    }

} 