<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 12:40
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\LettersModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoPasswordResetBegin implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("login")->getOrElseThrow(ControllerException::noArgument("login"));

        LettersModel::sendResetPasswordLetter($id);

    }

} 