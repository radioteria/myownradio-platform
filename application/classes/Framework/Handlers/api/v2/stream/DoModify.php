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
use Framework\Services\Http\HttpPost;
use Framework\Services\JsonResponse;

class DoModify implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getOrError("stream_id");

        $name = $post->getOrError("name");
        $info = $post->get("info")->orEmpty();
        $tags = $post->get("tags")->orEmpty();
        $permalink = $post->get("permalink")->orNull();
        $category = $post->get("category")->orNull();
        $access = $post->get("access")->getOrElse("PUBLIC");

        $stream = StreamModel::getInstance($id);

        $stream->update($name, $info, $permalink, $tags, $category, $access);

    }
} 