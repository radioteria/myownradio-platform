<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.12.14
 * Time: 14:02
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\JsonResponse;

class DoMe implements Controller
{
    public function doGet(AuthUserModel $userModel, JsonResponse $response)
    {
        $userId = $userModel->getID();

        $response->setHeaders([
            "User-Id: ${userId}"
        ]);
    }
} 
