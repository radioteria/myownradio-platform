<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 14:12
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Model\UsersModel;

class DoHack implements Controller {
    public function doPost(HttpPost $post) {
        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        UsersModel::authorizeById($id);
    }
} 