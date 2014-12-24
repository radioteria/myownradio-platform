<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:41
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\TrackModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoChangeColor implements Controller {
    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("track_id")->getOrElseThrow(ControllerException::noArgument("track_id"));
        $color = $post->getParameter("color_id")->getOrElseThrow(ControllerException::noArgument("color_id"));

        TrackModel::getInstance($id)->changeColor($color);

    }
} 