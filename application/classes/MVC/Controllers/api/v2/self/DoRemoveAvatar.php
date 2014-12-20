<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:56
 */

namespace MVC\Controllers\api\v2\self;


use Model\AuthUserModel;
use MVC\Controller;

class DoRemoveAvatar extends Controller {

    public function doPost(AuthUserModel $user) {
        $user->removeAvatar();
    }

} 