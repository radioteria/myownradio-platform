<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 18:25
 */

namespace MVC\Controllers\api\v2\stream;


use Model\Stream;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoAddTracks extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $tracks = $post->getParameter("tracks")->getOrElseThrow(ControllerException::noArgument("tracks"));

        $validator->validateTracksList($tracks);

        Stream::getInstance($id)->addTracks($tracks);

    }
} 