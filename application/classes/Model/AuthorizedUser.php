<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 13:10
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use MVC\Services\HttpSession;
use MVC\Services\Injectable;
use Tools\Singleton;

class AuthorizedUser extends User {

    use Singleton, Injectable;

    function __construct() {
        $uid = $this->getIdBySessionToken();
        parent::__construct($uid);
    }

    public function getIdBySessionToken() {
        $exception = ControllerException::noPermission();

        $token = HttpSession::getInstance()->get("TOKEN")->getOrElseThrow($exception);
        return $this->db->fetchOneColumn("SELECT b.uid FROM r_sessions a LEFT JOIN r_users b ON a.uid = b.uid WHERE a.token = ?",
            [$token])->getOrElseThrow($exception);
    }

}