<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:39
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\JsonResponse;

class DoShuffle implements Controller {

    public function doPost($stream_id, JsonResponse $response) {

        PlaylistModel::getInstance($stream_id)->shuffleTracks();

    }

} 