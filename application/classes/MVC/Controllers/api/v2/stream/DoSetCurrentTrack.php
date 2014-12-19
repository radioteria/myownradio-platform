<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 15.12.14
 * Time: 20:47
 */

namespace MVC\Controllers\api\v2\control;


use Model\StreamTrackListModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoSetCurrentTrack extends Controller {

    public function doPost(HttpPost $post) {

        $id     = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $track  = $post->getParameter("track")->getOrElseThrow(ControllerException::noArgument("track"));

        StreamTrackListModel::getInstance($id)->setPlayFrom($track);

    }

} 