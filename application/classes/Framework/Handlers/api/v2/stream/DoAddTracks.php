<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 18:25
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\Http\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;
use Tools\Optional\Mapper;

class DoAddTracks implements Controller {
    public function doPost(HttpPost $post, InputValidator $validator, JsonResponse $response) {

        $streamId = $post->getOrError("stream_id");
        $tracks = $post->getOrError("tracks");
        $upNext = $post->get("up_next")
            ->map(Mapper::toBoolean())->orFalse();

        $validator->validateTracksList($tracks);

        PlaylistModel::getInstance($streamId)->addTracks($tracks, $upNext);

    }
} 