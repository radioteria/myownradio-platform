<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:56
 */

namespace MVC\Controllers\api\v2\self;


use Model\AuthUserModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpFile;
use MVC\Services\JsonResponse;

class DoChangeAvatar extends Controller {

    public function doPost(HttpFile $file, AuthUserModel $user, JsonResponse $response) {

        $image = $file->getFirstFile()->getOrElseThrow(ControllerException::noImageAttached());

        $url = $user->changeAvatar($image);

        $response->setData($url);

    }

} 