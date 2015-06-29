<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 19.12.14
 * Time: 10:22
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\JsonResponse;

class DoPlayRandom implements Controller {

    public function doPost($stream_id, JsonResponse $response) {

        PlaylistModel::getInstance($stream_id)->scPlayRandom();

    }

} 