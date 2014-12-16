<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 15:33
 */

namespace MVC\Controllers\api\v2\stream;


use Model\StreamTrackList;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoRemoveTracks extends Controller {

    public function doPost(HttpPost $post) {

        $id     = $post->getParameter("id")     ->getOrElseThrow(ControllerException::noArgument("id"));
        $tracks = $post->getParameter("tracks") ->getOrElseThrow(ControllerException::noArgument("tracks"));

        StreamTrackList::getInstance($id)->removeTracks($tracks);

    }

} 