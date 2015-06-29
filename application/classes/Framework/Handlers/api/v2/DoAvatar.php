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
use Framework\Services\Http\HttpFile;
use Framework\Services\JsonResponse;

class DoAvatar implements Controller {

    public function doPost(HttpFile $file, AuthUserModel $user) {

        $image = $file->findAny()
            ->orThrow(ControllerException::noImageAttached());

        $url = $user->changeAvatar($image);

        return $url;

    }

    public function doDelete(AuthUserModel $user, JsonResponse $response) {
        $user->removeAvatar();
    }

    public function doGet(AuthUserModel $user) {
        return $user->getAvatarUrl();
    }

} 