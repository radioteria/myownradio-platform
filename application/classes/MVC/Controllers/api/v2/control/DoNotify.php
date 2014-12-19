<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:55
 */

namespace MVC\Controllers\api\v2\control;


use Model\PlaylistModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;

class DoNotify extends Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        PlaylistModel::getInstance($id)->notifyStreamers();

        $track = PlaylistModel::getInstance($id)->getPlayingTrack()->getOrElseNull();

        $response->setData($track->exportArray());

    }

} 