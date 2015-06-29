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
use Framework\Services\Http\HttpFile;
use Framework\Services\Http\HttpPost;

class DoCreate implements Controller {
    public function doPost(HttpPost $post, StreamsModel $model, HttpFile $file) {

        $name = $post->get("name")->orEmpty();
        $info = $post->get("info")->orEmpty();
        $tags = $post->get("tags")->orEmpty();
        $permalink = $post->get("permalink");
        $category = $post->get("category")->orNull();
        $access = $post->get("access")->getOrElse(StreamsModel::ACCESS_PUBLIC);

        $stream = $model->create($name, $info, $tags, $category, $permalink, $access);

        $file->findAny()->then(function ($file) use ($stream) {
            (new StreamModel($stream))->changeCover($file);
        });

        // Write out new stream object
        return $stream;

    }
} 