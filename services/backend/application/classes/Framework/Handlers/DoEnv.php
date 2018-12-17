<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 12:36
 */

namespace Framework\Handlers;

use Framework\Controller;
use Framework\Services\JsonResponse;

class DoEnv implements Controller
{
    public function doGet(JsonResponse $response)
    {
        $response->setData(secure($_ENV));
    }
}
