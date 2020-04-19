<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 15:19
 */

namespace Framework\Handlers\api\v2\stream;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpFiles;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Services;

class DoChangeCover implements Controller {

    function doPost(HttpPost $post, HttpFiles $file, Services $svc, JsonResponse $response) {

        $id = $post->getParameter("stream_id")
            ->getOrElseThrow(ControllerException::noArgument("stream_id"));
        $file = $file->getFirstFile()
            ->getOrElseThrow(new ControllerException("No image file attached"));

        $url = $svc->getStream($id)->changeCover($file);

        $response->setData($url);

    }

} 