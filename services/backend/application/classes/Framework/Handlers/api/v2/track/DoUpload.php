<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 14:40
 */

namespace Framework\Handlers\api\v2\track;

use Framework\Controller;
use Framework\Models\TracksModel;
use Framework\Services\HttpFiles;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoUpload implements Controller
{
    public function doPost(HttpFiles $file, HttpPost $post, TracksModel $model, JsonResponse $response)
    {
        ignore_user_abort(true);

        $streamID = $post->getParameter("stream_id");
        $upNext = boolval($post->getParameter("up_next")->getOrElseFalse());
        $skipCopies = true;

        $response->setData([
            "tracks" => $file->map(function ($file) use ($streamID, $model, $upNext, $skipCopies) {
                return $model->upload($file, $streamID, $upNext, $skipCopies);
            })
        ]);
    }
}
