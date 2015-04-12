<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:48
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoMoveTrack implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $uniqueId = $post->getParameter("unique_id")
            ->getOrElseThrow(ControllerException::noArgument("unique_id"));
        $index = $post->getParameter("new_index")
            ->getOrElseThrow(ControllerException::noArgument("new_index"));

        PlaylistModel::getInstance($id)->moveTrack($uniqueId, $index);

    }

} 