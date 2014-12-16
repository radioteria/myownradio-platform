<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 17:01
 */

namespace MVC\Controllers\api\v2\track;


use Model\Destructor;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoDelete extends Controller {

    public function doPost(HttpPost $post, Destructor $destructor) {

        $id = $post->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        $destructor->deleteTrack($id);

    }

} 