<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 10:17
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use LiqPay;
use Objects\AccountPlan;
use Objects\PaymentOrder;

class PaymentModel {
    /**
     * @param $plan_id
     * @return string
     */
    public static function createOrder($plan_id) {

        /**
         * @var UserModel $user
         * @var AccountPlan $plan
         */
        $user = AuthUserModel::getInstance();
        $plan = AccountPlan::getById($plan_id)
            ->getOrElseThrow(ControllerException::noAccountPlan($plan_id));

        $order = new PaymentOrder();
        $order->setUserId($user->getId());
        $order->setPlanId($plan_id);
        $order->setStatus(0);
        $order->setOrderDate(time());
        $order->save();

        $liqpay = LiqPay::Constr();

        $data = [
            'version'        => '3',
            'amount'         => $plan->getPlanValue(),
            'currency'       => 'USD',
            'description'    => $plan->getPlanName(),
            'order_id'       => $order->getOrderId(),
            'type'           => 'buy',
            'sandbox'        => 1,
            'subscribe'      => 1,
            'subscribe_date_start'  => "now",
            'subscribe_periodicity' => $plan->getPlanPeriod(),
            'server_url'     => "https://api.myownradio.biz/api/v3/payment"
        ];

        $html = $liqpay->cnb_hyperlink($data);

        return $html;

    }
} 