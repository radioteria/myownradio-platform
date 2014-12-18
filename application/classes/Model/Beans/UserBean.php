<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 14:48
 */

namespace Model\Beans;

/**
 * Class UserBean
 * @package Model\Beans
 * @table r_users
 * @key uid
 */
class UserBean implements BeanObject {

    use BeanTools;

    protected
        $uid, $mail, $login, $password,
        $name, $info, $rights, $registration_date,
        $last_visit_date, $permalink, $avatar;

    /**
     * @return mixed
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getLastVisitDate() {
        return $this->last_visit_date;
    }

    /**
     * @return mixed
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->mail;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getPermalink() {
        return $this->permalink;
    }

    /**
     * @return mixed
     */
    public function getRegistrationDate() {
        return $this->registration_date;
    }

    /**
     * @return mixed
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * @return mixed
     */
    public function getID() {
        return $this->uid;
    }




    /**
     * @param mixed $avatar
     * @return $this
     */
    public function setAvatar($avatar) {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @param mixed $info
     * @return $this
     */
    public function setInfo($info) {
        $this->info = $info;
        return $this;
    }

    /**
     * @param mixed $last_visit_date
     * @return $this
     */
    public function setLastVisitDate($last_visit_date) {
        $this->last_visit_date = $last_visit_date;
        return $this;
    }

    /**
     * @param mixed $login
     * @return $this
     */
    public function setLogin($login) {
        $this->login = $login;
        return $this;
    }

    /**
     * @param mixed $mail
     * @return $this
     */
    public function setMail($mail) {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $password
     * @return $this
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * @param mixed $permalink
     * @return $this
     */
    public function setPermalink($permalink) {
        $this->permalink = $permalink;
        return $this;
    }

    /**
     * @param mixed $registration_date
     * @return $this
     */
    public function setRegistrationDate($registration_date) {
        $this->registration_date = $registration_date;
        return $this;
    }

    /**
     * @param mixed $rights
     * @return $this
     */
    public function setRights($rights) {
        $this->rights = $rights;
        return $this;
    }

    /**
     * @param mixed $uid
     * @return $this
     */
    public function setUid($uid) {
        $this->uid = $uid;
        return $this;
    }




} 