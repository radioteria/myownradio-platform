<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 14:22
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Services;

class DoRemoveCover implements Controller {

    public function doPost(HttpPost $post, Services $svc, JsonResponse $response) {

        $id = $post->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        $svc->getStream($id)->removeCover();

    }

} 