<?php

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;

class HttpSession implements Injectable
{

    use Singleton;

    const SESSION_EXPIRE_FAST = 0;
    const SESSION_EXPIRE_MONTH = 2592000;

    public function __construct()
    {
        session_save_path(config('storage.session.save_path'));
        session_set_cookie_params(config('storage.session.expire_seconds'), null, null, false, true);
        session_start();
    }

    /**
     * @param $key
     * @return Optional
     */
    public function get($key)
    {
        return Optional::ofNullable(@$_SESSION[$key]);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * @return void;
     */
    public function destroy()
    {
        session_unset();
        session_destroy();
    }
}
