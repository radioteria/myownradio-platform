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
use Framework\Models\Factory;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoDelete implements Controller {

    public function doPost(HttpPost $post, Factory $fabric, JsonResponse $response) {
        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $fabric->deleteStream($id);
    }

} 