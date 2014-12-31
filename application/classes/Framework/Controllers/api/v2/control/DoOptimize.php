<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.12.14
 * Time: 9:21
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoOptimize implements Controller {
    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getRequired("stream_id");

        PlaylistModel::getInstance($id)->optimize();

    }
} 