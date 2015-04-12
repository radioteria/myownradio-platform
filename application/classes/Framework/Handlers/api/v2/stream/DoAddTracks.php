<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 18:25
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoAddTracks implements Controller {
    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response) {

        $id = $post->getParameter("stream_id")->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $tracks = $post->getParameter("tracks")->getOrElseThrow(ControllerException::noArgument("tracks"));
        $upNext = boolval($post->getParameter("up_next")->getOrElseFalse());

        $validator->validateTracksList($tracks);

        PlaylistModel::getInstance($id)->addTracks($tracks, $upNext);

    }
} 