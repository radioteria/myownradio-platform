<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 19:42
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;

class DoEmail extends ControllerImpl {
    public function doPost($field, TestFields $test) {

        return ["available" => !$test->testEmail($field)];

    }
} 