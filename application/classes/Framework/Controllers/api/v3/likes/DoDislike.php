<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 10.04.15
 * Time: 9:58
 */

namespace Framework\Controllers\api\v3\likes;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\DatabaseException;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Locale\I18n;

class DoDislike extends ControllerImpl {
    public function doPost(HttpPost $post, JsonResponse $response, DBQuery $dbq, AuthUserModel $userModel) {
        $track_id = $post->getRequired("track_id", FILTER_VALIDATE_INT);
        $query = $dbq->into("mor_track_like");
        $query->values("user_id", $userModel->getID());
        $query->values("track_id", $track_id);
        $query->values("relation", "dislike");
        try {
            $query->executeInsert();
        } catch (DatabaseException $ex) {
            throw ControllerException::of(I18n::tr("YOU_ALREADY_VOTED"));
        }
    }
} 