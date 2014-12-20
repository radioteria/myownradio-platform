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
use Framework\Services\HttpPost;
use Model\PlaylistModel;

class DoRemoveTracks extends Controller {

    public function doPost(HttpPost $post) {

        $id     = $post->getParameter("id")     ->getOrElseThrow(ControllerException::noArgument("id"));
        $tracks = $post->getParameter("tracks") ->getOrElseThrow(ControllerException::noArgument("tracks"));

        PlaylistModel::getInstance($id)->removeTracks($tracks);

    }

} 