<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 16:47
 */

namespace MVC\Controllers\api\v2\stream;


use Model\Fabric;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoDelete extends Controller {

    public function doPost(HttpPost $post, Fabric $fabric) {
        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $fabric->deleteStream($id);
    }

} 