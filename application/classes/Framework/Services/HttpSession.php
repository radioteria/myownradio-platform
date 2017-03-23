<?php

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;

class HttpSession implements Injectable
{
    use Singleton;

    private $started = false;

    public function __construct()
    {
        session_save_path(config('storage.session.save_path'));
        session_set_cookie_params(config('storage.session.expire_seconds'), "/", null, false, false);
    }

    private function startSession()
    {
        session_start();
    }

    private function lazyStartSession() {
        if (!$this->started) {
            $this->startSession();
            $this->started = true;
        }
    }

    /**
     * @param $key
     * @return Optional
     */
    public function get($key)
    {
        $this->lazyStartSession();
        return Optional::ofNullable(@$_SESSION[$key]);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->lazyStartSession();
        $_SESSION[$key] = $value;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        $this->lazyStartSession();
        return session_id();
    }

    /**
     * @return void;
     */
    public function destroy()
    {
        $this->lazyStartSession();
        session_unset();
        session_destroy();
    }
}
