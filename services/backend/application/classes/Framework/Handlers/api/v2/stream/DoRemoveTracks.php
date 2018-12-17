<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:33
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notifier;

class DoRemoveTracks implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getRequired("stream_id");
        $tracks = $post->getRequired("unique_ids");

        PlaylistModel::getInstance($id)->removeTracks($tracks);

    }

} 