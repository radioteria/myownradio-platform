<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 10:05
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Model\UsersModel;

class DoLogout implements Controller {

    public function doPost() {
        UsersModel::logout();
    }

} 