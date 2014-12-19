<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:24
 */

namespace MVC\Controllers\api\v2\control;


use Model\Factory;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;
use MVC\Services\JsonResponse;

class DoCreate extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator, Factory $factory, JsonResponse $response) {

        // Get user input parameters
        $name       = $post->getParameter("name")       ->getOrElseThrow(ControllerException::noArgument("name"));
        $info       = $post->getParameter("info")       ->getOrElseEmpty();
        $tags       = $post->getParameter("tags")       ->getOrElseEmpty();
        $permalink  = $post->getParameter("permalink")  ->getOrElseNull();
        $category   = $post->getParameter("category")   ->getOrElseNull();

        // Validate parameters
        $validator->validateStreamName($name);
        $validator->validateStreamPermalink($permalink);

        // Create new stream using fabric
        $stream = $factory->createStream($name, $info, $tags, $category, $permalink);

        // Write out new stream object
        $response->setData($stream);

    }
} 