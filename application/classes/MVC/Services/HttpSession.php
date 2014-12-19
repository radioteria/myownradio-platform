<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:07
 */

namespace MVC\Services;


use Tools\Optional;
use Tools\Singleton;

class HttpSession {
    use Singleton, Injectable;

    const SESSION_EXPIRE_FAST = 0;
    const SESSION_EXPIRE_MONTH = 2592000;

    public function __construct() {
        session_set_cookie_params(self::SESSION_EXPIRE_MONTH);
        session_start();
    }

    /**
     * @param $key
     * @return Optional
     */
    public function get($key) {
        return Optional::ofNull(@$_SESSION[$key]);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * @return string
     */
    public function getSessionId() {
        return session_id();
    }

    /**
     * @return void;
     */
    public function destroy() {
        session_unset();
        session_destroy();
    }

}