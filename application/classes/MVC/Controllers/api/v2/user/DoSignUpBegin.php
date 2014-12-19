<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 9:12
 */

namespace MVC\Controllers\api\v2\self;


use Model\LettersModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoSignUpBegin extends Controller {

    public function doPost(HttpPost $post, InputValidator $validator) {

        $email = $post->getParameter("email")->getOrElseThrow(ControllerException::noArgument("email"));

        $validator->validateEmail($email);
        $validator->validateUniqueUserEmail($email);

        LettersModel::sendRegistrationLetter($email);

    }

} 