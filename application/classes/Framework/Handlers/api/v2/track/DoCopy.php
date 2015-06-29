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
use Framework\Services\JsonResponse;
use Tools\Optional\Option;
use Tools\Optional\Transform;

class DoCopy implements Controller {
    public function doPost($track_id, $stream_id, Option $up_next,
                           TracksModel $model, JsonResponse $response) {

        $model->copy($track_id, $stream_id, $up_next->map(Transform::$toBoolean)->orFalse());
    }
} 