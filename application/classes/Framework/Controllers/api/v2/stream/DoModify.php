<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 17:22
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;
use Model\StreamModel;

class DoModify implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));

        $name = $post->getParameter("name")
            ->getOrElseThrow(ControllerException::noArgument("name"));
        $info       = $post->getParameter("info")->getOrElseEmpty();
        $tags       = $post->getParameter("tags")->getOrElseEmpty();
        $permalink  = $post->getParameter("permalink")->getOrElseNull();
        $category   = $post->getParameter("category")->getOrElseNull();
        $access     = $post->getParameter("access")->getOrElse('PUBLIC');

        $stream = StreamModel::getInstance($id);

        $stream->update($name, $info, $permalink, $tags, $category, $access);

    }
} 