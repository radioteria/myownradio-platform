<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 15:33
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\JsonResponse;

class DoRemoveTracks implements Controller {

    public function doPost($stream_id, $unique_ids, JsonResponse $response) {

        PlaylistModel::getInstance($stream_id)->removeTracks($unique_ids);

    }

} 