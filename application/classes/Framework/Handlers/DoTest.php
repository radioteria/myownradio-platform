<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;

class DoTest extends ControllerImpl {
    public function doGet() {
        $counter = makeCounter();
        echo $counter();
        echo $counter();
        echo $counter();
        $counter2 = makeCounter();
        echo $counter2();
    }
}

function makeCounter() {
    $value = 0;
    return function () use (&$value) {
        return $value ++;
    };
}