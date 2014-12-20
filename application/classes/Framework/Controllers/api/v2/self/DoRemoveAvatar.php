<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:56
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Model\AuthUserModel;

class DoRemoveAvatar implements Controller {

    public function doPost(AuthUserModel $user) {
        $user->removeAvatar();
    }

} 