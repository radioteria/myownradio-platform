<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:56
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpFiles;
use Framework\Services\JsonResponse;
use Model\AuthUserModel;

class DoChangeAvatar implements Controller {

    public function doPost(HttpFiles $file, AuthUserModel $user, JsonResponse $response) {

        $image = $file->getFirstFile()->getOrElseThrow(ControllerException::noImageAttached());

        $url = $user->changeAvatar($image);

        $response->setData($url);

    }

} 