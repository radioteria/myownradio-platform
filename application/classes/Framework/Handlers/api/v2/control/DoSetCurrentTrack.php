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
use Framework\Services\Notifier;

class DoSetCurrentTrack implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, Notifier $notif1er) {

        $id     = $post->getRequired("stream_id");
        $track  = $post->getRequired("unique_id");

        PlaylistModel::getInstance($id)->scPlayByUniqueID($track);

    }

} 