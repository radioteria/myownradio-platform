<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 15:19
 */

namespace MVC\Controllers\api\v2\stream;


use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpFile;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;
use MVC\Services\Services;

class DoChangeCover extends Controller {

    function doPost(HttpPost $post, HttpFile $file, Services $svc, JsonResponse $response) {

        $id = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $file = $file->getFirstFile()->getOrElseThrow(new ControllerException("No image file attached"));

        $url = $svc->getStream($id)->changeCover($file);

        $response->setData($url);

    }

} 