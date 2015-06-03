<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Objects\User;

class DoTest extends ControllerImpl {
    public function doGet() {
        User::getByID(1)->getOrElseThrow(ControllerException::className());
    }
}