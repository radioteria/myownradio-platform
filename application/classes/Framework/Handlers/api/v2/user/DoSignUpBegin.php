<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 9:12
 */

namespace Framework\Handlers\api\v2\user;


use Framework\Controller;
use Framework\Models\LettersModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoSignUpBegin implements Controller {

    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response, DBQuery $query) {

        $email = $post->getRequired("email");
        //$code = $post->getRequired("code");

        $validator->validateEmail($email);
        $validator->validateUniqueUserEmail($email);

        // Validate code
//        $query->selectFrom("mor_invite", "code", $code)->fetchOneColumn()
//            ->then(function ($code) use ($query) { $query->deleteFrom("mor_invite", "code", $code)->update(); })
//            ->justThrow(new ControllerException("Incorrect invite code entered"));

        LettersModel::sendRegistrationLetter($email);

    }

} 