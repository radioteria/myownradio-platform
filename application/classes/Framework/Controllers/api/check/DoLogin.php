<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 18:25
 */

namespace Framework\Controllers\api\check;


use Framework\Controller;
use Framework\ControllerImpl;
use Framework\Preferences;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoLogin extends ControllerImpl {
    public function doPost(HttpPost $post, JsonResponse $response, DBQuery $query, Preferences $preferences) {
        $field = $post->getRequired("field");

        if (array_search($field, $preferences->get("invalid", "login")->get()) !== false) {
            $response->setData(["available" => false]);
        } else {
            $count = !boolval(count($query->selectFrom("r_users")->where("login", $field)));
            $response->setData(["available" => $count]);
        }

    }
} 