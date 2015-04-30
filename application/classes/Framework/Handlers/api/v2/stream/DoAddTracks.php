<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 18:25
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;
use Framework\Services\Notif1er;

class DoAddTracks implements Controller {
    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response, Notif1er $notif1er) {

        $id = $post->getRequired("stream_id");
        $tracks = $post->getRequired("tracks");
        $upNext = $post->getParameter("up_next", FILTER_VALIDATE_BOOLEAN)->getOrElseFalse();

        $validator->validateTracksList($tracks);

        PlaylistModel::getInstance($id)->addTracks($tracks, $upNext);

        $notif1er->event("tracklist", $id, "state_change", null);

    }
} 