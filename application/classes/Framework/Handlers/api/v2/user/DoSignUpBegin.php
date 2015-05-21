<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 9:12
 */

namespace Framework\Handlers\api\v2\user;


use Business\Validator\BusinessValidator;
use Framework\Controller;
use Framework\Models\LettersModel;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoSignUpBegin implements Controller {

    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response) {

        $email = $post->getRequired("email");
        BusinessValidator::validateEmail($email);
        LettersModel::sendRegistrationLetter($email);

    }

} 