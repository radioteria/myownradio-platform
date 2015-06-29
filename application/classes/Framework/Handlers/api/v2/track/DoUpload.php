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
use Framework\Services\Http\HttpFile;
use Framework\Services\Http\HttpPost;
use Tools\Optional\Transform;

class DoUpload implements Controller {

    public function doPost(HttpFile $file, HttpPost $post, TracksModel $model) {

        ignore_user_abort(true);

        $streamId = $post->get("stream_id");
        $upNext = $post->get("up_next")->map(Transform::$toBoolean)->orFalse();
        $skipCopies = true;

        $uploaded = [];

        $file->each(function ($file) use ($streamId, $model, $upNext, &$uploaded, &$skipCopies) {
            if (is_array($file["name"])) {
                for ($i = 0; $i < count($file["name"]); $i++) {
                    $tmp = [
                        "name" => $file["name"][$i],
                        "type" => $file["type"][$i],
                        "tmp_name" => $file["tmp_name"][$i],
                        "error" => $file["error"][$i],
                        "size" => $file["size"][$i]
                    ];
                    $uploaded[] = $model->upload($tmp, $streamId, $upNext, $skipCopies);
                }
            } else {
                $uploaded[] = $model->upload($file, $streamId, $upNext, $skipCopies);
            }
        });

        return ["tracks" => $uploaded];

    }

} 