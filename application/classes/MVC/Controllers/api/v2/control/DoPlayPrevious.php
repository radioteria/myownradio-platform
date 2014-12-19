<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:14
 */

namespace MVC\Controllers\api\v2\control;


use Model\StreamTrackListModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;

class DoPlayPrevious extends Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        StreamTrackListModel::getInstance($id)->scPlayPrevious();

        $track = StreamTrackListModel::getInstance($id)->getPlayingTrack()->getOrElseNull();

        $response->setData($track->exportArray());

    }

} 