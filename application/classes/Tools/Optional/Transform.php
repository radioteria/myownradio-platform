<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.06.15
 * Time: 15:36
 */

namespace Tools\Optional;


use Objects\User;

class Transform {

    /* Basic transformations */
    public static $toBoolean;

    /* Model transformations */
    public static $userToId;
    public static $trim;

    public static function init() {
        self::$toBoolean = function ($v) {
            return boolval($v);
        };
        self::$userToId = function (User $user) {
            return $user->getId();
        };
        self::$trim = function ($v) {

        };
    }
}

Transform::init();