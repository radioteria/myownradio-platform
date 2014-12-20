<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 18:25
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Model\PlaylistModel;

class DoAddTracks extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $tracks = $post->getParameter("tracks")->getOrElseThrow(ControllerException::noArgument("tracks"));

        $validator->validateTracksList($tracks);

        PlaylistModel::getInstance($id)->addTracks($tracks);

    }
} 