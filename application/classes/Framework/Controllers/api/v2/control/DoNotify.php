<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 16:55
 */

namespace Framework\Controllers\api\v2\control;


use Framework\Context;
use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\PlaylistModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoNotify implements Controller {

    public function doPost(Context $context, JsonResponse $response) {

        PlaylistModel::getInstance($context->getStreamID())->notifyStreamers();

    }

} 