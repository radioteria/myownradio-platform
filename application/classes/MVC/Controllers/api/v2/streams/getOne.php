<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:53
 */

namespace MVC\Controllers\api\v2\streams;

use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpRequest;
use MVC\Services\HttpResponse;
use REST\Streams;

class getOne extends Controller {
    public function doGet(HttpRequest $request, HttpResponse $response, Streams $streams) {
        $id = $request->getParameters()->getParameter("id")
            ->getOrElseThrow(new ControllerException("Stream ID is not specified"));

        $response->setData($streams->getOneStream($id));
    }
} 