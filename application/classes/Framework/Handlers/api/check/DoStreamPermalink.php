<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.02.15
 * Time: 14:49
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;
use Tools\Optional\Option;

class DoStreamPermalink extends ControllerImpl {
    public function doPost($field, Option $context, /*AuthUserModel $user, */
                           TestFields $test) {

        $result = $test->testStreamPermalink($field);

        return array("available" => $result === false || $result == $context->orNull());

    }
} 