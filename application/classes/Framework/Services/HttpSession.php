<?php

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;

class HttpSession implements Injectable
{
    use Singleton;

    private $modified = false;
    private $session = [];
    private $sessionId = null;

    public function __construct()
    {
        $this->readSession();
    }

    private function readSession()
    {
        $this->sessionId = $_COOKIE['secure_session_id'] ?? uniqid();
        $session = array_key_exists('secure_session', $_COOKIE)
            ? json_decode($_COOKIE['secure_session'], true)
            : [];
        $this->session = is_array($session) ? $session : [];
    }

    public function isModified()
    {
        return $this->modified;
    }

    public function sendToClient()
    {
        $data = json_encode($this->session);
        setcookie('secure_session', $data, 0, '/');
        setcookie('secure_session_id', $this->sessionId, 0, '/');
    }

    /**
     * @param $key
     * @return Optional
     */
    public function get($key)
    {
        return Optional::ofNullable(
            $this->session[$key] ?? null
        );
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->modified = true;
        $this->session[$key] = $value;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}
