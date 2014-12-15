<?php

namespace Model;

use MVC\Exceptions\ControllerException;
use MVC\Exceptions\UnauthorizedException;
use MVC\Services\HttpSession;
use MVC\Services\Injectable;
use Tools\Singleton;

class User extends Model {

    use Singleton, Injectable;

    private $userId;
    private $userLogin;
    private $userName;
    private $userEmail;
    private $userInfo;

    private $userToken;
    
    public function __construct() {

        parent::__construct();

        $uid = $this->getIdBySessionToken();
        $user = $this->db->fetchOneRow("SELECT * FROM r_users WHERE uid = ?", array($uid))
            ->getOrElseThrow(ControllerException::noPermission());

        $this->userId       = intval($user['uid']);
        $this->userLogin    = $user['login'];
        $this->userName     = $user['name'];
        $this->userEmail    = $user['mail'];
        $this->userInfo     = $user['info'];

    }
    
    public function getId() {
        return $this->userId;
    }
    
    public function getLogin() {
        return $this->userLogin;
    }
    
    public function getEmail() {
        return $this->userEmail;
    }

    public function getName() {
        return $this->userName;
    }

    public function getToken() {
        return $this->userToken;
    }

    public function changePassword($password) {
        $newPassword = md5($this->getLogin() . $password);
        $this->db->executeUpdate("UPDATE r_users SET password = ? WHERE uid = ?", array($newPassword, $this->userId));
    }

    /**
     * @param mixed $email
     * @return self
     */
    public function setUserEmail($email) {
        $this->userEmail = $email;
        return $this;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name) {
        $this->userName = $name;
        return $this;
    }

    /**
     * @param mixed $info
     * @return self
     */
    public function setInfo($info) {
        $this->userInfo = $info;
        return $this;
    }


    public function getIdBySessionToken() {
        $exception = ControllerException::noPermission();
        $token = HttpSession::getInstance()->get("TOKEN")->getOrElseThrow($exception);
        return $this->db->fetchOneColumn("SELECT b.uid FROM r_sessions a LEFT JOIN r_users b ON a.uid = b.uid WHERE a.token = ?",
            [$token])->getOrElseThrow($exception);
    }

    public function update() {
        $this->db->executeUpdate("UPDATE r_users SET name = ?, info = ?, mail = ? WHERE uid = ?",
        [$this->userName, $this->userInfo, $this->userEmail, $this->userId]);
    }



}