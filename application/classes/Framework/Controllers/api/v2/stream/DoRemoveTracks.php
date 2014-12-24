<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:33
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoRemoveTracks implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $tracks = $post->getParameter("unique_ids")
            ->getOrElseThrow(ControllerException::noArgument("unique_ids"));

        PlaylistModel::getInstance($id)->removeTracks($tracks);

    }

} 