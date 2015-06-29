<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 03.01.15
 * Time: 00:03
 */

namespace Framework\Handlers\api\exists;


use Business\Test\TestFields;
use Framework\ControllerImpl;

class DoLogin extends ControllerImpl {
    public function doPost($field, TestFields $test) {
        return array("exists" => $test->testLogin($field));
    }
} 