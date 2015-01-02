<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 19:42
 */

namespace Framework\Controllers\api\check;


use Framework\Controller;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoEmail implements Controller {
    public function doPost(HttpPost $post, JsonResponse $response, DBQuery $query) {
        $field = $post->getRequired("field");
        $count = !boolval(count($query->selectFrom("r_users")->where("mail", $field)));
        $response->setData(["available" => $count]);
    }
} 