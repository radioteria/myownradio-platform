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
use Framework\Services\Http\HttpFile;
use Framework\Services\Http\HttpPost;
use Framework\Services\Services;

class DoChangeCover implements Controller {

    function doPost(HttpPost $post, HttpFile $file, Services $svc) {

        $streamId = $post->getOrError("stream_id");
        $file = $file->findAny()
            ->getOrThrow(new ControllerException("No image file attached"));

        return $svc->getStream($streamId)->changeCover($file);

    }

} 