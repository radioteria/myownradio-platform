<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 15.12.14
 * Time: 20:47
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notif1er;

class DoSetCurrentTrack implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, Notif1er $notif1er) {

        $id     = $post->getRequired("stream_id");
        $track  = $post->getRequired("unique_id");

        PlaylistModel::getInstance($id)->scPlayByUniqueID($track);

        $notif1er->notify("mor:channel:play_from", ["channel_id" => $id, "unique_id" => $track]);

    }

} 