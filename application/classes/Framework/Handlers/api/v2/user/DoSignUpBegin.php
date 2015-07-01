<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 9:12
 */

namespace Framework\Handlers\api\v2\user;


use Business\Forms\SignUpStartForm;
use Framework\Controller;
use Framework\Models\LettersModel;
use Framework\Services\JsonResponse;

class DoSignUpBegin implements Controller {

    public function doPost(JsonResponse $response, SignUpStartForm $form) {

        LettersModel::sendRegistrationLetter($form->getEmail());

    }

} 