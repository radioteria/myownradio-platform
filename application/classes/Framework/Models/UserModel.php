<?php

namespace Framework\Models;

use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Models\Traits\Stats;
use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\InputValidator;
use Framework\Services\ValidatorTemplates;
use Objects\AccountPlan;
use Objects\Payment;
use Objects\Stream;
use Objects\Track;
use Objects\User;
use REST\Users;
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

    /** @var User $user */
    private $user;

    /** @var AccountPlan $planObject */
    private $planObject, $planExpires;

    public function __construct() {

        parent::__construct();

        if (func_num_args() == 1 && is_numeric(func_get_arg(0))) {

            $id = func_get_arg(0);

            $this->user = User::getByID($id)->getOrElseThrow(
                UnauthorizedException::noUser($id)
            );

        } elseif (func_num_args() == 1) {

            $key = func_get_arg(0);

            $this->user = User::getByFilter("FIND_BY_KEY_PARAMS", [":key" => $key])
                ->getOrElseThrow(
                    UnauthorizedException::noUserByLogin($key)
                );

        } elseif (func_num_args() == 2) {

            $login = func_get_arg(0);
            $password = func_get_arg(1);

            $this->user = User::getByFilter("FIND_BY_CREDENTIALS", [":login" => $login, ":password" => $password])
                ->getOrElseThrow(UnauthorizedException::wrongLogin());

        } else {

            throw ApplicationException::of("Incorrect number of arguments");

        }

        $this->loadAccountQuote();
        $this->userID = $this->user->getID();
        $this->loadStats();

    }

    public function loadAccountQuote() {
        $defaultPlan = AccountPlan::getByID(1)->get();
        /** @var Payment $actualPayment */
        $actualPayment = Payment::getByFilter("ACTUAL", [$this->getID()])->getOrElseNull();
        if ($actualPayment === null) {
            $this->planExpires = time();
            $this->planObject = $defaultPlan;
            return null;
        } else {
            $this->planExpires = $actualPayment->getExpires();
            $this->planObject = AccountPlan::getByID($actualPayment->getPlanId())->getOrElse($defaultPlan);
        }
    }

    /**
     * @return mixed
     * @deprecated
     */
    public function getCurrentPlanExpires() {
        return $this->planExpires;
    }

    /**
     * @return AccountPlan
     */
    public function getCurrentPlan() {
        return $this->planObject;
    }

    public function getID() {
        return $this->user->getId();
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

    public function changePassword($newPassword, $oldPassword) {

        if (!password_verify($oldPassword, $this->user->getPassword())) {
            throw UnauthorizedException::wrongPassword();
        }

        $this->changePasswordNow($newPassword);

    }

    public function changePasswordNow($password) {
        ValidatorTemplates::validatePassword($password);
        $crypt = password_hash($password, PASSWORD_DEFAULT);
        $this->user->setPassword($crypt)->save();
    }

    /**
     * @param AccountPlan $plan
     * @param $source
     * @param string $data
     */
    public function changeAccountPlan(AccountPlan $plan, $source, $data = "") {
        $payment = new Payment();
        $payment->setUserId($this->user->getId());
        $payment->setPlanId($plan->getPlanId());
        $payment->setPaymentSource($source);
        $payment->setPaymentComment($data);
        $payment->setExpires($this->planExpires + $plan->getPlanDuration());
        $payment->save();
    }

    public function getDisplayName() {

        return $this->getName() ?: $this->getLogin();

    }

    public function edit($name, $info, $permalink, $country = 0) {

        $this->user->setName($name);
        $this->user->setInfo($info);
        $this->user->setPermalink($permalink);
        $this->user->setCountryId($country);

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
        $random = Common::generateUniqueId();
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

    /**
     * @return null|string
     */
    public function getAvatarUrl() {
        return $this->user->getAvatarUrl();
    }

    /**
     * @param $password
     * @throws UnauthorizedException
     */
    public function checkPassword($password) {
        if (!password_verify($password, $this->user->getPassword())) {
            throw UnauthorizedException::wrongPassword();
        }
    }

    /**
     * Account Delete
     * todo: Delete user's avatars and covers
     */
    public function delete() {

        /** @var Stream[] $streams */
        $streams = Stream::getListByFilter("uid", [$this->user->getId()]);

        foreach ($streams as $stream) {
            (new DeleteQuery("r_link"))
                ->where("stream_id", $stream->getID())->update();
            $stream->delete();
        }

        /** @var Track[] $tracks */
        $tracks = Track::getListByFilter("uid", [$this->user->getId()])
            ->wrap(TrackModel::className());

        foreach ($tracks as $track) { $track->delete(); }

        $this->user->delete();

    }

    /**
     *
     */
    public function touchLastLoginDate() {
        $this->user->setLastVisitDate(time())->save();
    }

    /**
     * @return mixed
     */
    public function toRestFormat() {
        return Users::getInstance()->getUserByID($this->getID(), true);
    }

}