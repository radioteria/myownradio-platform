<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 19.12.14
 * Time: 10:22
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Model\PlaylistModel;

class DoPlayRandom extends Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        PlaylistModel::getInstance($id)->scPlayRandom();

    }

} 