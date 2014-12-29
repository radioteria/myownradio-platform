<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 29.12.14
 * Time: 13:38
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\TracksModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoCopyCurrent implements Controller {

    public function doPost(HttpPost $post, TracksModel $model, JsonResponse $response) {

        $upNext = boolval($post->getParameter("up_next")->getOrElseFalse());

        $fromStreamID = $post->getParameter("from_id")->getOrElseThrow(ControllerException::noArgument("stream_id"));

        $streamID = $post->getParameter("stream_id");

        $model->grabCurrentTrack($fromStreamID, $streamID, $upNext);

    }

} 