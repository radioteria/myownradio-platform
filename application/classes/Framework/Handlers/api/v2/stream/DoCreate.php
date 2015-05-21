<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:24
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\StreamModel;
use Framework\Models\StreamsModel;
use Framework\Services\HttpFiles;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoCreate implements Controller {
    public function doPost(HttpPost $post, StreamsModel $model,
                           JsonResponse $response, HttpFiles $file) {

        $name       = $post->getParameter("name")       ->getOrElseEmpty();
        $info       = $post->getParameter("info")       ->getOrElseEmpty();
        $tags       = $post->getParameter("tags")       ->getOrElseEmpty();
        $permalink  = $post->getParameter("permalink");
        $category   = $post->getParameter("category")   ->getOrElseNull();
        $access     = $post->getParameter("access")     ->getOrElse(StreamsModel::ACCESS_PUBLIC);

        $stream = $model->create($name, $info, $tags, $category, $permalink, $access);

        $file->getFirstFile()->then(function ($file) use ($stream) {
            (new StreamModel($stream))->changeCover($file);
        });

        // Write out new stream object
        $response->setData($stream);

    }
} 