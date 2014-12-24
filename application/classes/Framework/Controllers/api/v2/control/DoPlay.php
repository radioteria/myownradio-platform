<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:49
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoPlay implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));

        PlaylistModel::getInstance($id)->scPlay();

    }

} 