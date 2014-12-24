<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:39
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoShuffle implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));

        PlaylistModel::getInstance($id)->shuffleTracks();

    }

} 