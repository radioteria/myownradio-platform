<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:48
 */

namespace Objects;

/**
 * Class Subscription
 * @package Model\ActiveRecords
 * @table r_subscriptions
 * @key id
 */
class Subscription extends ActiveRecordObject implements ActiveRecord {

    protected $id, $uid, $plan, $payment_info, $expire;

    /**
     * @param mixed $expire
     */
    public function setExpire($expire) {
        $this->expire = $expire;
    }

    /**
     * @return mixed
     */
    public function getExpire() {
        return $this->expire;
    }

    /**
     * @return mixed
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @param mixed $payment_info
     */
    public function setPaymentInfo($payment_info) {
        $this->payment_info = $payment_info;
    }

    /**
     * @return mixed
     */
    public function getPaymentInfo() {
        return $this->payment_info;
    }

    /**
     * @param mixed $plan
     */
    public function setPlan($plan) {
        $this->plan = $plan;
    }

    /**
     * @return mixed
     */
    public function getPlan() {
        return $this->plan;
    }

    /**
     * @param mixed $uid
     */
    public function setUserID($uid) {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getUserID() {
        return $this->uid;
    }



} 