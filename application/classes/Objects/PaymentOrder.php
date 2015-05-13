<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 10:23
 */

namespace Objects;
use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class PaymentOrder
 * @package Objects
 * @table mor_payment_order
 * @key order_id
 */
class PaymentOrder extends ActiveRecordObject implements ActiveRecord {
    private $order_id;
    private $user_id;
    private $plan_id;
    private $status;
    private $order_date;

    /**
     * @return mixed
     */
    public function getOrderDate() {
        return $this->order_date;
    }

    /**
     * @return mixed
     */
    public function getOrderId() {
        return $this->order_id;
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
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * @param $order_date
     */
    public function setOrderDate($order_date) {
        $this->order_date = $order_date;
    }

    /**
     * @param mixed $plan_id
     */
    public function setPlanId($plan_id) {
        $this->plan_id = $plan_id;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }


} 