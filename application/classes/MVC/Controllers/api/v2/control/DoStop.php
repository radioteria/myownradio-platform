<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:30
 */

namespace MVC\Controllers\api\v2\control;


use Model\PlaylistModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoStop extends Controller {

    public function doPost(HttpPost $post) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        PlaylistModel::getInstance($id)->scStop();

    }

} 