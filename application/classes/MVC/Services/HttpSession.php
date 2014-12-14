<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 15:07
 */

namespace MVC\Services;


use Tools\Singleton;

class HttpSession {
    use Singleton, Injectable;

    const SESSION_EXPIRE_FAST = 0;
    const SESSION_EXPIRE_MONTH = 2592000;

    public function __construct($expire = self::SESSION_EXPIRE_FAST) {
        session_set_cookie_params($expire);
        session_start();
    }

    public function get($key) {
        return $_SESSION[$key];
    }

    public function put($key, $value) {
        $_SESSION[$key] = $value;
    }
}