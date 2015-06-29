<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 15.12.14
 * Time: 20:47
 */

namespace Framework\Handlers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\JsonResponse;

class DoSetCurrentTrack implements Controller {

    public function doPost($stream_id, $unique_id, JsonResponse $response) {

        PlaylistModel::getInstance($stream_id)
            ->scPlayByUniqueID($unique_id);

    }

} 