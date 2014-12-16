<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use MVC\Controller;
use MVC\Services\HttpGet;
use MVC\Services\JsonResponse;

class DoTest extends Controller {
    public function doGet(HttpGet $get, JsonResponse $response) {

        $get->getParameter("id")

            ->then(function ($id) use ($response) {

                $response->setMessage("ID = " . $id);

            })

            ->otherwise(function () use ($response) {

                $response->setMessage("No ID :(");

            });

    }
} 