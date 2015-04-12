<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 31.03.15
 * Time: 12:05
 */

namespace Framework\Handlers\api\v2\track;


use Framework\Controller;
use Framework\Models\TracksModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoCopy implements Controller {
    public function doPost(HttpPost $post, TracksModel $model, JsonResponse $response) {
        $track_id = $post->getRequired("track_id");
        $dst_stream = $post->getParameter("stream_id");
        $up_next = $post->getParameter("up_next")->getOrElse(0);
        $model->copy($track_id, $dst_stream, $up_next);
    }
} 