<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:48
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Subscription
 * @package Model\ActiveRecords
 * @table r_subscriptions
 * @key sub_id
 */
class Subscription extends ActiveRecordObject implements ActiveRecord {

    protected $sub_id, $user_id, $plan_id, $payment_info, $expire;

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
        return $this->sub_id;
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
        $this->plan_id = $plan;
    }

    /**
     * @return mixed
     */
    public function getPlan() {
        return $this->plan_id;
    }

    /**
     * @param mixed $uid
     */
    public function setUserID($uid) {
        $this->user_id = $uid;
    }

    /**
     * @return mixed
     */
    public function getUserID() {
        return $this->user_id;
    }


}