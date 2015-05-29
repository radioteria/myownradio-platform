<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Framework\Preferences;

class DoTest extends ControllerImpl {
    public function doGet() {
        echo Preferences::getSetting("validator", "user.login.min");
    }
}