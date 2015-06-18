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
        echo call_user_func($counter->inc);
        echo call_user_func($counter->inc);
        echo call_user_func($counter->dec);
        $counter2 = makeCounter();
        echo call_user_func($counter2->dec);
    }
}

function makeCounter() {
    $value = 0;
    return (object) [
        "inc" => function () use (&$value) { return ++ $value; },
        "dec" => function () use (&$value) { return ++ $value; },
    ];
}