<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:07
 */

namespace Framework\Services\Validators;


use Framework\Exceptions\ControllerException;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\Validator;

class UserValidator extends Validator {
    /**
     * @param $user_key
     * @throws ControllerException
     * @return object user_object
     */
    public function validateUserByKey($user_key) {
        $user_object = (new SelectQuery("r_users"))
            ->where("(uid = :key) OR (permalink = :key AND permalink IS NOT NULL)", [":key" => $user_key])
            ->fetchOneRow()->getOrElseThrow(ControllerException::noUser($user_key));
        return $user_object;
    }
} 