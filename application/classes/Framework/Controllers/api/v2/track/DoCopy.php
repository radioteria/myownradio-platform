<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.12.14
 * Time: 12:34
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\TracksModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoCopy implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, TracksModel $model) {

        $trackID = $post->getParameter("track_id")
            ->getOrElseThrow(ControllerException::noArgument("track_id"));

        $streamID = $post->getParameter("stream_id");

        $model->grabTrack($trackID, $streamID);

    }

} 