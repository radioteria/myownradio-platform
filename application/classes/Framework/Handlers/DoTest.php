<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Tools\Functional\Range;

class DoTest extends ControllerImpl {
    public function doGet() {
        return (new Range(0, 20))->odd();
    }
}