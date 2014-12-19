<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 15.12.14
 * Time: 20:47
 */

namespace MVC\Controllers\api\v2\stream;


use Model\StreamTrackList;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoSetCurrentTrack extends Controller {

    public function doPost(HttpPost $post) {

        $id     = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $track  = $post->getParameter("track")->getOrElseThrow(ControllerException::noArgument("track"));

        StreamTrackList::getInstance($id)->setPlayFrom($track);

    }

} 