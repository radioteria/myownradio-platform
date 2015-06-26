<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Objects\User;
use Tools\Optional;

class DoTest extends ControllerImpl {
    public function doGet() {

        $user = User::getByID(1);

        echo $user->getName()->get();

    }
}

