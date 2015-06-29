<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 18:25
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;

class DoLogin extends ControllerImpl {
    public function doPost($field, TestFields $test) {

        return array("available" => !$test->testLogin($field));

    }
} 