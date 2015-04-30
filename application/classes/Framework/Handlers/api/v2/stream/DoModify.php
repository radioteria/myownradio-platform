<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 17:22
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\StreamModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notif1er;

class DoModify implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, Notif1er $notif1er) {

        $id = $post->getRequired("stream_id");

        $name = $post->getRequired("name");
        $info = $post->getParameter("info")->getOrElseEmpty();
        $tags = $post->getParameter("tags")->getOrElseEmpty();
        $permalink = $post->getParameter("permalink")->getOrElseNull();
        $category = $post->getParameter("category")->getOrElseNull();
        $access = $post->getParameter("access")->getOrElse("PUBLIC");

        $stream = StreamModel::getInstance($id);

        $stream->update($name, $info, $permalink, $tags, $category, $access);

        $notif1er->notify("mor:channel:modify", $id);

    }
} 