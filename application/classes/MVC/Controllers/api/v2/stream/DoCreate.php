<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 15:24
 */

namespace MVC\Controllers\api\v2\stream;


use Model\Fabric;
use Model\User;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\JsonResponse;
use MVC\Services\InputValidator;

class DoCreate extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator, Fabric $fabric, User $user, JsonResponse $response) {

        // Get user input parameters
        $name = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $info = $post->getParameter("info")->getOrElseEmpty();
        $tags = $post->getParameter("tags")->getOrElseEmpty();
        $permalink = $post->getParameter("permalink")->getOrElseNull();
        $category = $post->getParameter("category")->getOrElseNull();

        // Validate parameters
        $validator->validateStreamName($name);
        $validator->validateStreamPermalink($permalink);

        // Create new stream using fabric
        $creator = $user->getId();
        $stream = $fabric->createStream($name, $info, $tags, $category, $permalink, $creator);

        // Write out new stream object
        $response->setData($stream);

    }
} 