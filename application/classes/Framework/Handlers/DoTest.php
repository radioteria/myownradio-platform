<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Tools\Optional;
use Tools\Optional\Option;

class DoTest extends ControllerImpl {
    public function doGet(Optional $id) {

        $option   = Option::ofNullable($id->getOrElseNull());

        $func1    = function ($n) { return $n / 10; };
        $func2    = function ($n) { return 50 / $n; };
        $notNull  = function ($n) { return $n != 0; };

        $filtered = $option->map($func1)->filter($notNull)->map($func2);

        echo $filtered->getOrElse("Wrong value!");

    }
}

