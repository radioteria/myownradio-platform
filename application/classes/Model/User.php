<?php

namespace Model;

use Model\Traits\Stats;
use MVC\Exceptions\ControllerException;
use MVC\Services\Database;
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

            $key = func_get_arg(0);

            $user = Database::doInTransaction(function(Database $db) use ($key) {
                return $db->fetchOneRow("SELECT * FROM r_users WHERE uid = :id OR mail = :id", [":id" => $key])
                    ->getOrElseThrow(
                        new ControllerException(sprintf("User with login or email '%s' not exists", $key))
                    );
            });


        } elseif (func_num_args() == 2) {

            $login = func_get_arg(0);
            $password = func_get_arg(1);

            $user = Database::doInTransaction(function(Database $db) use ($login, $password) {
                return $db->fetchOneRow("SELECT * FROM r_users WHERE login = ? AND password = ?", [$login, $password])
                    ->getOrElseThrow(ControllerException::noPermission());
            });

        } else {

            throw new \Exception("Incorrect number of arguments");

        }

        $active = Database::doInTransaction(function (Database $db) use ($user) {

            $query = $db->getDBQuery()->selectFrom("r_subscriptions");
            $query->select("*");
            $query->where("uid", $user["uid"]);
            $query->where("expire > UNIX_TIMESTAMP(NOW())");
            $query->addOrderBy("id DESC");
            $query->limit(1);

            return $db->fetchOneRow($query)
                ->getOrElse(["plan" => 0, "expire" => null]);

        });


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

        Database::doInTransaction(function (Database $db) use ($newPassword) {
            $db->executeUpdate("UPDATE r_users SET password = ? WHERE uid = ?",
                array($newPassword, $this->userID));
            $db->commit();
        });

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

        Database::doInTransaction(function (Database $db) {
            $db->executeUpdate("UPDATE r_users SET name = ?, info = ?, mail = ? WHERE uid = ?",
                [$this->userName, $this->userInfo, $this->userEmail, $this->userID]);
            $db->commit();
        });

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