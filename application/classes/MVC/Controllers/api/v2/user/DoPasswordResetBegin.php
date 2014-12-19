<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 12:40
 */

namespace MVC\Controllers\api\v2\self;


use Model\Letters;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoPasswordResetBegin extends Controller {

    public function doPost(HttpPost $post) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        Letters::sendResetPasswordLetter($id);

    }

} 