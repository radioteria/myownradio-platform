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

class DoTest extends ControllerImpl {
    public function doGet() {

        $object = User::getByID();

        echo $object->getName();

    }
}

