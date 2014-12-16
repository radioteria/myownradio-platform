<?php

namespace Model;

use Model\Traits\Stats;
use MVC\Exceptions\ControllerException;
use MVC\Services\Injectable;
use Tools\Singleton;

/**
 * Class User
 * @package Model
 */
class User extends Model {

    use Stats;

    protected $userID;
    private $userLogin;
    private $userName;
    private $userEmail;
    private $userInfo;
    private $userPassword;

    private $userToken;
    private $activePlan;
    private $planExpire;

    private $modifiedFlag = false;
    
    public function __construct() {

        parent::__construct();

        if (func_num_args() == 1) {

            $user = $this->db->fetchOneRow("SELECT * FROM r_users WHERE uid = :id OR mail = :id",
                [":id" => func_get_arg(0)])
                ->getOrElseThrow(new ControllerException(sprintf("User with login or email '%s' not exists",
                    func_get_arg(0))));

        } elseif (func_num_args() == 2) {

            $user = $this->db->fetchOneRow("SELECT * FROM r_users WHERE login = ? AND password = ?",
                array(func_get_arg(0), func_get_arg(1)))
                ->getOrElseThrow(ControllerException::noPermission());

        } else {

            throw new \Exception("Incorrect number of arguments");

        }

        $active = $this->db->fetchOneRow("SELECT * FROM r_subscriptions
            WHERE uid = ? AND expire > UNIX_TIMESTAMP(NOW()) ORDER BY id DESC LIMIT 1", [$user["uid"]])
            ->getOrElse(["plan" => 0, "expire" => null]);

        $this->userID       = intval($user['uid']);
        $this->userLogin    = $user['login'];
        $this->userName     = $user['name'];
        $this->userEmail    = $user['mail'];
        $this->userInfo     = $user['info'];
        $this->userPassword = $user["password"];

        $this->activePlan   = intval($active["plan"]);
        $this->planExpire   = intval($active["expire"]);

        $this->loadStats();

    }

    /**
     * @return mixed
     */
    public function getActivePlanId() {
        return $this->activePlan;
    }

    /**
     * @return mixed
     */
    public function getActivePlanExpire() {
        return $this->planExpire;
    }

    /**
     * @return Plan
     */
    public function getActivePlan() {
        return Plan::getInstance($this->activePlan);
    }

    public function getId() {
        return $this->userID;
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
        $this->db->executeUpdate("UPDATE r_users SET password = ? WHERE uid = ?", array($newPassword, $this->userID));
    }

    /**
     * @param mixed $email
     * @return self
     */
    public function setUserEmail($email) {
        $this->userEmail = $email;
        $this->modifiedFlag = true;

        return $this;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name) {
        $this->userName = $name;
        $this->modifiedFlag = true;

        return $this;
    }

    /**
     * @param mixed $info
     * @return self
     */
    public function setInfo($info) {
        $this->userInfo = $info;
        $this->modifiedFlag = true;

        return $this;
    }

    public function update() {
        $this->db->executeUpdate("UPDATE r_users SET name = ?, info = ?, mail = ? WHERE uid = ?",
        [$this->userName, $this->userInfo, $this->userEmail, $this->userID]);
        $this->modifiedFlag = false;
    }

    public function __destruct() {
        if ($this->modifiedFlag) {
            $this->update();
        }
    }

    public function getPassword() {

        return $this->userPassword;

    }

    public function getDisplayName() {

        return empty($this->getName()) ? $this->getLogin() : $this->getName();

    }


}