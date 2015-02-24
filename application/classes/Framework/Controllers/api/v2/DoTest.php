<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 12:37
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Injector\Injector;
use Framework\Injector\TestClass;
use Framework\Services\JsonResponse;

class DoTest implements Controller {
    public function doGet(JsonResponse $response, Injector $injector) {
        $test = new TestClass();
        $response->setData($injector->call([$test, "callThis"]));
    }
} 