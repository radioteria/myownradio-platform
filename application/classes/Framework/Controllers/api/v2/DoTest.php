<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 12:37
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\JsonResponse;

class DoTest implements Controller {
    public function doGet(JsonResponse $response, AuthUserModel $model) {
        $response->setData($model->getCurrentPlan());
    }
} 