<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 15:19
 */

namespace Framework\Controllers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpFile;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Services;

class DoChangeCover implements Controller {

    function doPost(HttpPost $post, HttpFile $file, Services $svc, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $file = $file->getFirstFile()->getOrElseThrow(new ControllerException("No image file attached"));

        $url = $svc->getStream($id)->changeCover($file);

        $response->setData($url);

    }

} 