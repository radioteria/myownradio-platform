<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 10:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\Invoker;
use Framework\Services\JsonResponse;

class DoTest implements Controller {

    public function doGet() {
        Invoker::invoke(function (JsonResponse $response) {

        });
    }

}