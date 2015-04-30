<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:49
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notif1er;

class DoPlay implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, Notif1er $notif1er) {

        $id = $post->getRequired("stream_id");

        PlaylistModel::getInstance($id)->scPlay();

        $notif1er->notify("mor:channel:play", $id);

    }

} 