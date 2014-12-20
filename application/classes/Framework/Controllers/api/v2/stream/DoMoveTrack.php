<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:48
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Model\PlaylistModel;

class DoMoveTrack implements Controller {

    public function doPost(HttpPost $post) {

        $id        = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $uniqueId  = $post->getParameter("unique_id")->getOrElseThrow(ControllerException::noArgument("unique_id"));
        $index     = $post->getParameter("new_index")->getOrElseThrow(ControllerException::noArgument("new_index"));

        PlaylistModel::getInstance($id)->moveTrack($uniqueId, $index);

    }

} 