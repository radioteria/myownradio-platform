<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:10
 */

namespace Framework\Handlers\api\v2\users;


use Framework\Controller;
use Framework\Services\Http\HttpGet;
use REST\Users;

class DoGetAll implements Controller {
    public function doGet(HttpGet $get, Users $users) {
        $offset = $get->get("offset")->orNull();
        $limit = $get->get("limit")->orNull();
        $filter = $get->get("filter")->orNull();

        return $users->getUsersList($filter, $limit, $offset);
    }
} 