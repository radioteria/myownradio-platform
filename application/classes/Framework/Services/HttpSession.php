<?php

namespace Framework\Services;

use Exception;
use \Firebase\JWT\JWT;

use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;


class HttpSession implements Injectable
{
    use Singleton;

    const SESSION_COOKIE_NAME = 'secure_session';
    const SESSION_EXPIRE_SECONDS = 30 * 24 * 60 * 60; // 30 days

    private $modified = false;
    private $session = [];
    private $sessionId = null;

    public function __construct()
    {
        $this->readSession();
    }

    private function readSession()
    {
        $key = config('jwt.key');

        if (array_key_exists(self::SESSION_COOKIE_NAME, $_COOKIE)) {
            try {
                $decodedSessionData = JWT::decode(
                    $_COOKIE[self::SESSION_COOKIE_NAME], $key, ['HS256']
                );
                $this->sessionId = $decodedSessionData->id ?? uniqid();
                $this->session = (array) $decodedSessionData->data ?? [];
                return;
            } catch (Exception $exception) {
                //
            }
        }

        $this->sessionId = uniqid();
        $this->session = [];
    }

    public function sendIfModified()
    {
        if ($this->isModified()) {
            if (headers_sent()) {
                error_log('Headers already sent but session was modified!');
                return;
            }

            $this->sendToClient();
        }
    }

    public function isModified()
    {
        return $this->modified;
    }

    public function sendToClient()
    {
        $key = config('jwt.key');
        $sessionData = [
            'id' => $this->sessionId,
            'data' => $this->session
        ];

        $encodedSessionData = JWT::encode($sessionData, $key, 'HS256');

        setcookie(
            self::SESSION_COOKIE_NAME, $encodedSessionData,
            self::SESSION_EXPIRE_SECONDS + time(),'/'
        );
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
