<?php

namespace Model;

use Framework\Exceptions\ControllerException;
use Framework\Services\Database;
use Framework\Services\InputValidator;
use Model\Traits\Stats;
use Objects\Subscription;
use Objects\User;
use Tools\Common;
use Tools\File;
use Tools\Folders;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class UserModel
 * @package Model
 */
class UserModel extends Model implements SingletonInterface {

    use Stats, Singleton;

    protected $userID;

    private $activePlan;
    private $planExpire;

    /** @var User $user */
    private $user;

    public function __construct() {

        parent::__construct();

        if (func_num_args() == 1 && is_numeric(func_get_arg(0))) {

            $id = func_get_arg(0);

            $this->user = User::getByID($id)->getOrElseThrow(
                    new ControllerException(sprintf("User with id '%s' not exists", $id)));

        } elseif (func_num_args() == 1) {

            $key = func_get_arg(0);

            $this->user = User::getByFilter("FIND_BY_KEY_PARAMS", [":id" => $key])
                ->getOrElseThrow(
                    new ControllerException(sprintf("User with login or email '%s' not exists", $key))
                );

        } elseif (func_num_args() == 2) {

            $login = func_get_arg(0);
            $password = func_get_arg(1);

            $this->user = User::getByFilter("FIND_BY_CREDENTIALS", [$login, $password])
                ->getOrElseThrow(ControllerException::wrongLogin());

        } else {

            throw new \Exception("Incorrect number of arguments");

        }

        $active = $this->readActivePlan();

        $this->activePlan   = intval($active["plan"]);
        $this->planExpire   = intval($active["expire"]);
        $this->userID       = $this->user->getID();

        $this->loadStats();

    }

    /**
     * @return mixed
     */
    private function readActivePlan() {

        return Database::doInConnection(function (Database $db) {

            $query = $db->getDBQuery()->selectFrom("r_subscriptions");
            $query->select("*");
            $query->where("uid", $this->user->getID());
            $query->where("expire > UNIX_TIMESTAMP(NOW())");
            $query->orderBy("id DESC");
            $query->limit(1);

            return $db->fetchOneRow($query)
                ->getOrElse(["plan" => 0, "expire" => null]);

        });

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
     * @return PlanModel
     */
    public function getActivePlan() {
        return PlanModel::getInstance($this->activePlan);
    }

    public function getID() {
        return $this->user->getID();
    }
    
    public function getLogin() {
        return $this->user->getLogin();
    }
    
    public function getEmail() {
        return $this->user->getEmail();
    }

    public function getName() {
        return $this->user->getName();
    }

    public function getPassword() {
        return $this->user->getPassword();
    }

    public function changePassword($password) {

        $validator = InputValidator::getInstance();

        $validator->validatePassword($password);

        $newPassword = md5($this->getLogin() . $password);

        $this->user->setPassword($newPassword)->save();

    }

    public function changeActivePlan(PlanModel $plan, BasisModel $basis) {

        $object = new Subscription();
        $object->setUserID($this->getID());
        $object->setPlan($plan->getID());
        $object->setPaymentInfo($basis->getInfo());
        $object->setExpire(time() + $basis->getDuration());
        $object->save();

    }

    public function getDisplayName() {

        return empty($this->getName()) ? $this->getLogin() : $this->getName();

    }

    public function edit($name, $info, $email) {

        $validator = InputValidator::getInstance();

        $validator->validateEmail($email);

        $this->user->setName($name);
        $this->user->setInfo($info);
        $this->user->setEmail($email);

        $this->user->save();

    }


    public function removeAvatar() {

        $folders = Folders::getInstance();

        if (!is_null($this->user->getAvatar())) {

            $file = new File($folders->genAvatarPath($this->user->getAvatar()));

            if ($file->exists()) {
                $file->delete();
            }

            $this->user->setAvatar(null)->save();

        }

    }

    public function changeAvatar($file) {

        $folders = Folders::getInstance();

        $validator = InputValidator::getInstance();

        $validator->validateImageMIME($file["tmp_name"]);

        $random = Common::generateUniqueID();

        $this->removeAvatar();

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);


        $newImageFile = sprintf("avatar%05d_%s.%s", $this->userID, $random, strtolower($extension));
        $newImagePath = $folders->genAvatarPath($newImageFile);

        $result = move_uploaded_file($file['tmp_name'], $newImagePath);

        if ($result !== false) {

            $this->user->setAvatar($newImageFile)->save();

            return $folders->genAvatarUrl($newImageFile);

        } else {

            return null;

        }

    }

    public function touchLastLoginDate() {
        $this->user->setLastVisitDate(time())->save();
    }

}