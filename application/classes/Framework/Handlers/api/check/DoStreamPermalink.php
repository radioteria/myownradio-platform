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
use Framework\Models\AuthUserModel;
use Framework\Services\ValidatorTemplates;
use Tools\Optional;

class DoStreamPermalink extends ControllerImpl {
    public function doPost($field, Optional $context, AuthUserModel $user, TestFields $test) {

        $result = $test->testStreamPermalink($field);
        return ["available" => $result === false || $result == $context->getOrElseNull()];

    }
} 