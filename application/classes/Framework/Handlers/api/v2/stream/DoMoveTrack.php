<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:48
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\Http\HttpPost;
use Framework\Services\JsonResponse;

class DoMoveTrack implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getOrError("stream_id");
        $uniqueId = $post->getOrError("unique_id");
        $index = $post->getOrError("new_index");

        PlaylistModel::getInstance($id)->moveTrack($uniqueId, $index);

    }

} 