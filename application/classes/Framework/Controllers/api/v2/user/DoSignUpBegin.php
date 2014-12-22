<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 9:12
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;
use Model\LettersModel;

class DoSignUpBegin implements Controller {

    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response) {

        $email = $post->getParameter("email")->getOrElseThrow(ControllerException::noArgument("email"));

        $validator->validateEmail($email);
        $validator->validateUniqueUserEmail($email);

        LettersModel::sendRegistrationLetter($email);

    }

} 