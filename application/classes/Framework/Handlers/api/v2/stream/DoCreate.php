<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:24
 */

namespace Framework\Handlers\api\v2\stream;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Models\StreamsModel;
use Framework\Services\HttpFiles;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notifier;
use Framework\Services\Services;
use REST\Streams;

class DoCreate implements Controller {
    public function doPost(HttpPost $post, StreamsModel $model,
                           JsonResponse $response, HttpFiles $file, Services $svc, Notifier $notif1er) {

        // Get user input parameters
        $name = $post->getRequired("name");
        $info = $post->getParameter("info")->getOrElseEmpty();
        $tags = $post->getParameter("tags")->getOrElseEmpty();
        $permalink = $post->getParameter("permalink");
        $category = $post->getParameter("category")->getOrElseNull();
        $access = $post->getParameter("access")->getOrElse(StreamsModel::ACCESS_PUBLIC);

        // Create new stream using fabric
        $stream = $model->create($name, $info, $tags, $category, $permalink, $access);

        $file->getFirstFile()->then(function ($file) use ($svc, $stream) {
            /** $svc Services */
            $svc->getStream($stream)->changeCover($file);
        });

        // Write out new stream object
        $response->setData($stream);

    }
} 