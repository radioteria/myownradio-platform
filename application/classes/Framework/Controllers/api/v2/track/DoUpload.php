<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 14:40
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Services\HttpFile;
use Framework\Services\HttpPost;
use Model\TracksModel;

class DoUpload implements Controller {

    public function doPost(HttpFile $file, HttpPost $post, TracksModel $model) {

        $streamID   = $post->getParameter("id");

        $file->each(function ($file) use ($streamID, $model) {
            $model->upload($file, $streamID);
        });

    }

} 