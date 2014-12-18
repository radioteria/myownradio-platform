<?php

namespace Model;

use Model\ActiveRecords\UserAR;
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

    use Stats, Singleton;

    protected $userID;

    private $activePlan;
    private $planExpire;

    /** @var UserAR */
    private $userBean;
    
    public function __construct() {

        parent::__construct();

        if (func_num_args() == 1 && is_numeric(func_get_arg(0))) {

            $id = func_get_arg(0);

            $this->userBean = UserAR::getByID($id)->getOrElseThrow(
                    new ControllerException(sprintf("User with id '%s' not exists", $id)));

        } elseif (func_num_args() == 1) {

            $key = func_get_arg(0);

            $this->userBean = UserAR::getByFilter("FIND_BY_KEY_PARAMS", [":id" => $key])
                ->getOrElseThrow(
                    new ControllerException(sprintf("User with login or email '%s' not exists", $key))
                );

        } elseif (func_num_args() == 2) {

            $login = func_get_arg(0);
            $password = func_get_arg(1);

            $this->userBean = UserAR::getByFilter("FIND_BY_CREDENTIALS", [$login, $password])
                ->getOrElseThrow(ControllerException::noPermission());

        } else {

            throw new \Exception("Incorrect number of arguments");

        }

        $active = Database::doInConnection(function (Database $db) {

            $query = $db->getDBQuery()->selectFrom("r_subscriptions");
            $query->select("*");
            $query->where("uid", $this->userBean->getID());
            $query->where("expire > UNIX_TIMESTAMP(NOW())");
            $query->addOrderBy("id DESC");
            $query->limit(1);

            return $db->fetchOneRow($query)
                ->getOrElse(["plan" => 0, "expire" => null]);

        });


/*        $this->userID       = intval($user['uid']);
        $this->userLogin    = $user['login'];
        $this->userName     = $user['name'];
        $this->userEmail    = $user['mail'];
        $this->userInfo     = $user['info'];
        $this->userPassword = $user["password"];*/

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
        return $this->userBean->getID();
    }
    
    public function getLogin() {
        return $this->userBean->getLogin();
    }
    
    public function getEmail() {
        return $this->userBean->getEmail();
    }

    public function getName() {
        return $this->userBean->getName();
    }


    public function changePassword($password) {

        $newPassword = md5($this->getLogin() . $password);

        $this->userBean->setPassword($newPassword)->save();

    }

    /**
     * @param mixed $email
     * @return self
     */
    public function setUserEmail($email) {
        $this->userBean->setEmail($email);
        return $this;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name) {
        $this->userBean->setName($name);
        return $this;
    }

    /**
     * @param mixed $info
     * @return self
     */
    public function setInfo($info) {
        $this->userBean->setInfo($info);
        return $this;
    }

    public function update() {

        $this->userBean->save();

    }

    public function getDisplayName() {

        return empty($this->getName()) ? $this->getLogin() : $this->getName();

    }

    /**
     * @return mixed|UserAR
     */
    public function getBean() {
        return $this->userBean;
    }


}