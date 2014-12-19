<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:39
 */

namespace MVC\Controllers\api\v2\stream;


use Model\StreamTrackList;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoShuffle extends Controller {

    public function doPost(HttpPost $post) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        StreamTrackList::getInstance($id)->shuffleTracks();

    }

} 