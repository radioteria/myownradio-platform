<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:24
 */

namespace MVC\Controllers\api\v2\stream;


use Model\StreamsModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;

class DoCreate extends Controller {
    public function doPost(HttpPost $post, StreamsModel $model, JsonResponse $response) {

        // Get user input parameters
        $name       = $post->getParameter("name")       ->getOrElseThrow(ControllerException::noArgument("name"));
        $info       = $post->getParameter("info")       ->getOrElseEmpty();
        $tags       = $post->getParameter("tags")       ->getOrElseEmpty();
        $category   = $post->getParameter("category")   ->getOrElseNull();
        $permalink  = $post->getParameter("permalink");

        // Create new stream using fabric
        $stream = $model->create($name, $info, $tags, $category, $permalink);

        // Write out new stream object
        $response->setData($stream);

    }
} 