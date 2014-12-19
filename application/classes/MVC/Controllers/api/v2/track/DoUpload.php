<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 14:40
 */

namespace MVC\Controllers\api\v2\track;


use Model\Factory;
use MVC\Controller;
use MVC\Services\HttpFile;
use MVC\Services\HttpPost;

class DoUpload extends Controller {

    public function doPost(HttpPost $post, HttpFile $file, Factory $factory) {

        $streamID   = $post->getParameter("id");

        $file->each(function ($file) use ($streamID, $factory) {
            $factory->uploadFile($file, $streamID);
        });

    }

} 