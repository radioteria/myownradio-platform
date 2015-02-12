<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:24
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\StreamsModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoCreate implements Controller {
    public function doPost(HttpPost $post, StreamsModel $model, JsonResponse $response) {

        // Get user input parameters
        $name = $post->getRequired("name");
        $info = $post->getParameter("info")->getOrElseEmpty();
        $tags = $post->getParameter("tags")->getOrElseEmpty();
        $category = $post->getParameter("category")->getOrElseNull();
        $permalink = $post->getParameter("permalink");

        // Create new stream using fabric
        $stream = $model->create($name, $info, $tags, $category, $permalink);

        // Write out new stream object
        $response->setData($stream);

    }
} 