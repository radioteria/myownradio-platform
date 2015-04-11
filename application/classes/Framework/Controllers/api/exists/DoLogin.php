<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 03.01.15
 * Time: 00:03
 */

namespace Framework\Controllers\api\exists;


use Framework\Controller;
use Framework\ControllerImpl;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoLogin extends ControllerImpl {
    public function doPost(HttpPost $post, DBQuery $query, JsonResponse $response) {
        $field = $post->getRequired("field");
        $response->setData([
            "exists" => boolval(count($query
                ->selectFrom("r_users")
                ->where("login = :id OR mail = :id", [":id" => $field]))
            )]);
    }
} 