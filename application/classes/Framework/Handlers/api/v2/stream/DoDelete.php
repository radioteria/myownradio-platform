<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 16:47
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\StreamModel;
use Framework\Services\JsonResponse;

class DoDelete implements Controller {

    public function doPost($stream_id, JsonResponse $response) {

        StreamModel::getInstance($stream_id)->delete();

    }

} 