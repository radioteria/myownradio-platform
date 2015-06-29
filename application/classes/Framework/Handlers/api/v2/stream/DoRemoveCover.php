<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 14:22
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Services\JsonResponse;
use Framework\Services\Services;

class DoRemoveCover implements Controller {

    public function doPost($stream_id, Services $svc, JsonResponse $response) {

        $svc->getStream($stream_id)->removeCover();

    }

} 