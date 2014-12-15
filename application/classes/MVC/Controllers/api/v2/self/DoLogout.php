<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 10:05
 */

namespace MVC\Controllers\api\v2\self;


use Model\Users;
use MVC\Controller;

class DoLogout extends Controller {

    public function doPost() {
        Users::unAuthorize();
    }

} 