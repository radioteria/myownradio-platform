<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 9:12
 */

namespace Framework\Handlers\api\v2\user;


use Business\Validator\BusinessValidator;
use Business\Validator\Entity\UserValidatorException;
use Framework\Controller;
use Framework\Models\LettersModel;
use Framework\Services\JsonResponse;

class DoSignUpBegin implements Controller {

    public function doPost($email, JsonResponse $response) {

        (new BusinessValidator($email))
            ->isEmail()
            ->throwOnFail(UserValidatorException::newIncorrectEmail())
            ->isEmailAvailable()
            ->throwOnFail(UserValidatorException::newUnavailableEmail());

        LettersModel::sendRegistrationLetter($email);

    }

} 