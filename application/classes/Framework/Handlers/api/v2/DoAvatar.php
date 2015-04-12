<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.12.14
 * Time: 14:06
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpFiles;
use Framework\Services\JsonResponse;

class DoAvatar implements Controller {

    public function doPost(HttpFiles $file, AuthUserModel $user, JsonResponse $response) {

        logger(print_r($_FILES, true));

        $image = $file->getFirstFile()
            ->getOrElseThrow(ControllerException::noImageAttached());

        $url = $user->changeAvatar($image);

        $response->setData($url);

    }

    public function doDelete(AuthUserModel $user, JsonResponse $response) {
        $user->removeAvatar();
    }

    public function doGet(AuthUserModel $user, JsonResponse $response) {
        $response->setData($user->getAvatarUrl());
    }

} 