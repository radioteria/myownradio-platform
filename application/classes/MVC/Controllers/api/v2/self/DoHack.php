<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 14:12
 */

namespace MVC\Controllers\api\v2\self;


use Model\Users;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoHack extends Controller {
    public function doPost(HttpPost $post) {
        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        Users::authorizeById($id);
    }
} 