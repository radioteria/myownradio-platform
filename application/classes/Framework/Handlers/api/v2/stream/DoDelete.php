<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 16:47
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Models\Factory;
use Framework\Models\StreamModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Notif1er;

class DoDelete implements Controller {

    public function doPost(HttpPost $post, Factory $fabric, JsonResponse $response, Notif1er $notif1er) {

        $id = $post->getRequired("stream_id");
        $model = StreamModel::getInstance($id);
        $model->delete();

        $notif1er->event("channel", $id, "delete", null);

    }

} 