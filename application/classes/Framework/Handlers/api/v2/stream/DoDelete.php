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
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoDelete implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response) {

        $id = $post->getRequired("stream_id");
        StreamModel::getInstance($id)->delete();

    }

} 