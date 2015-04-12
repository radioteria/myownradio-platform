<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:10
 */

namespace Framework\Handlers\api\v2\users;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Users;

class DoGetAll implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, Users $users) {
        $offset = $get->getParameter("offset")->getOrElseNull();
        $limit = $get->getParameter("limit")->getOrElseNull();
        $filter = $get->getParameter("filter")->getOrElseNull();

        $response->setData($users->getUsersList($filter, $limit, $offset));
    }
} 