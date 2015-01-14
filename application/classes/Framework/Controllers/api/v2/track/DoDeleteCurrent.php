<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.01.15
 * Time: 14:45
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\TracksModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Objects\PlaylistTrack;

class DoDeleteCurrent implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, TracksModel $model) {

        $streamID = $post->getRequired("stream_id");

        /** @var PlaylistTrack $current */
        $current = PlaylistTrack::getCurrent($streamID)
            ->getOrElseThrow(ControllerException::of("Nothing playing on stream #" . $streamID));

        // Delete tracks from streams if they appears
        $model->deleteFromStreams($current->getID());

        // Delete tracks from service
        $model->delete($current->getID());

    }

} 