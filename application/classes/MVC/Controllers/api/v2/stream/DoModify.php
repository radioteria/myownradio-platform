<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 17:22
 */

namespace MVC\Controllers\api\v2\stream;


use Model\Stream;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;
use MVC\Services\InputValidator;

class DoModify extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $name = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $info = $post->getParameter("info")->getOrElseEmpty();
        $tags = $post->getParameter("tags")->getOrElseEmpty();
        $permalink = $post->getParameter("permalink")->getOrElseNull();
        $category = $post->getParameter("category")->getOrElseNull();

        $validator->validateStreamName($name);
        $validator->validateStreamPermalink($permalink, $id);

        $stream = Stream::getInstance($id);

        $stream->setName($name);
        $stream->setInfo($info);
        $stream->setHashtags($tags);
        $stream->setPermalink($permalink);
        $stream->setCategory($category);

        $stream->save();

        $response->setData($stream->getName());

    }
} 