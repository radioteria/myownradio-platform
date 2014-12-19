<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 19.12.14
 * Time: 10:22
 */

namespace MVC\Controllers\api\v2\control;


use Model\PlaylistModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;

class DoPlayRandom extends Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        PlaylistModel::getInstance($id)->scPlayRandom();

        $track = PlaylistModel::getInstance($id)->getPlayingTrack()->getOrElseNull();

        $response->setData($track->exportArray());

    }

} 