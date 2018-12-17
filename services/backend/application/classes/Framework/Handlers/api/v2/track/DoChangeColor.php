<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:41
 */

namespace Framework\Handlers\api\v2\track;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoChangeColor implements Controller {

    public function doPost(HttpPost $post, JsonResponse $response, InputValidator $validator,
                           DBQuery $dbq, AuthUserModel $user) {

        $id = $post->getRequired("track_id");
        $color = $post->getRequired("color_id");

        $validator->validateTrackColor($color);

        $query = $dbq->updateTable("r_tracks")
            ->where("uid", $user->getID())
            ->where("tid", explode(",", $id));

        $query->set("color", $color);
        $query->update();

    }

} 