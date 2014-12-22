<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 16:47
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Model\Factory;

class DoDelete implements Controller {

    public function doPost(HttpPost $post, Factory $fabric, JsonResponse $response) {
        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $fabric->deleteStream($id);
    }

} 