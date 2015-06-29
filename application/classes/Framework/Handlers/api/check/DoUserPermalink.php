<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 15:24
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;
use Framework\Models\AuthUserModel;

class DoUserPermalink extends ControllerImpl {
    public function doPost($field, AuthUserModel $user, TestFields $test) {

        $result = $test->testUserPermalink($field);

        return array("available" => $result === false || $result == $user->getID());

    }
} 