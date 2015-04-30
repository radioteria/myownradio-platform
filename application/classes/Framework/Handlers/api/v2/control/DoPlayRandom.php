<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 19.12.14
 * Time: 10:22
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notif1er;

class DoPlayRandom implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, Notif1er $notif1er) {

        $id = $post->getRequired("stream_id");

        PlaylistModel::getInstance($id)->scPlayRandom();

        $notif1er->event("tracklist", $id, "state_change", null);

    }

} 