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
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Model\TrackModel;

class DoChangeColor implements Controller {
    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("track_id")->getOrElseThrow(ControllerException::noArgument("id"));
        $color = $post->getParameter("color")->getOrElseThrow(ControllerException::noArgument("color"));

        TrackModel::getInstance($id)->changeColor($color);

    }
} 