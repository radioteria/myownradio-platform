<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:09
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Controller;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoPlayNext implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getRequired("stream_id");

        PlaylistModel::getInstance($id)->scPlayNext();

    }

}