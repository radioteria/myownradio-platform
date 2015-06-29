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
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoChangeColor implements Controller {

    public function doPost($track_id, $color_id, JsonResponse $response, InputValidator $validator,
                           DBQuery $dbq, AuthUserModel $user) {

        $validator->validateTrackColor($color_id);

        $query = $dbq->updateTable("r_tracks")
            ->where("uid", $user->getID())
            ->where("tid", explode(",", $track_id));

        $query->set("color", $color_id);
        $query->update();

    }

} 