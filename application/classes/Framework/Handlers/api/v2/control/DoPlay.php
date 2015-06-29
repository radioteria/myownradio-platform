<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:49
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\JsonResponse;

class DoPlay implements Controller {

    public function doPost($stream_id, JsonResponse $response) {

        PlaylistModel::getInstance($stream_id)->scPlay();

    }

} 