<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 19.12.14
 * Time: 10:22
 */

namespace MVC\Controllers\api\v2\stream;


use Model\StreamTrackList;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoPlayRandomTrack extends Controller {

    public function doPost(HttpPost $post) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        StreamTrackList::getInstance($id)->setRandomTrack();

    }

} 