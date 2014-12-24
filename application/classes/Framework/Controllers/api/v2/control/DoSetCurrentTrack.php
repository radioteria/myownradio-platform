<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 15.12.14
 * Time: 20:47
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoSetCurrentTrack implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("stream_id")->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $track = $post->getParameter("unique_id")->getOrElseThrow(ControllerException::noArgument("unique_id"));

        PlaylistModel::getInstance($id)->scPlayByUniqueID($track);

    }

} 