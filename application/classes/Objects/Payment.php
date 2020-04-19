<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 12:26
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Payments
 * @package Objects
 * @table mor_payments
 * @key payment_id
 * @do_ACTUAL (user_id = ? AND expires > UNIX_TIMESTAMP(NOW()) AND success) ORDER BY payment_id DESC
 */
class Payment extends ActiveRecordObject implements ActiveRecord {
    private
        $payment_id,
        $user_id,
        $plan_id,
        $expires,
        $payment_comment,
        $payment_source,
        $success,
        $modified;

    function beforeUpdate() {
        $this->modified = time();
        return true;
    }

    /**
     * @return mixed
     */
    public function getExpires() {
        return $this->expires;
    }

    /**
     * @return mixed
     */
    public function getPaymentComment() {
        return $this->payment_comment;
    }

    /**
     * @return mixed
     */
    public function getPaymentId() {
        return $this->payment_id;
    }

    /**
     * @return mixed
     */
    public function getPaymentSource() {
        return $this->payment_source;
    }

    /**
     * @return mixed
     */
    public function getPlanId() {
        return $this->plan_id;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * @param mixed $expires
     */
    public function setExpires($expires) {
        $this->expires = $expires;
    }

    /**
     * @param mixed $payment_comment
     */
    public function setPaymentComment($payment_comment) {
        $this->payment_comment = $payment_comment;
    }

    /**
     * @param mixed $payment_source
     */
    public function setPaymentSource($payment_source) {
        $this->payment_source = $payment_source;
    }

    /**
     * @param mixed $plan_id
     */
    public function setPlanId($plan_id) {
        $this->plan_id = $plan_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess($success) {
        $this->success = $success;
    }

    /**
     * @return mixed
     */
    public function getSuccess() {
        return $this->success;
    }



} 