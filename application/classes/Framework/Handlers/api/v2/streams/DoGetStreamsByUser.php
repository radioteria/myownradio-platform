<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:52
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use REST\Streams;
use REST\Users;

class DoGetStreamsByUser implements Controller {

    public function doGet($user, Streams $streams, Users $users) {

        return [
            "user" => $users->getUserByID($user),
            "streams" => $streams->getByUser($user)
        ];

    }

} 