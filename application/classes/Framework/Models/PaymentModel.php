<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 10:17
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use Framework\View\Errors\View500Exception;
use LiqPay;
use Objects\AccountPlan;
use Objects\Payment;

/**
 * Class PaymentModel
 * @package Framework\Models
 * @localized 21.05.2015
 */
class PaymentModel {
    public static function confirmOrder($order_id, $data = null) {
        /** @var Payment $payment */
        $payment = Payment::getById($order_id)
            ->orThrow(new View500Exception("Wrong order_id"));
        $payment->setSuccess(1);
        $payment->setPaymentComment($data);
        $payment->save();
    }

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
            ->orThrow(ControllerException::noAccountPlan($plan_id));

        $payment = new Payment();
        $payment->setExpires(time() + $plan->getPlanDuration());
        $payment->setPaymentSource("LIQPAY");
        $payment->setSuccess(0);
        $payment->setPaymentComment("");
        $payment->setPlanId($plan->getPlanId());
        $payment->setUserId($user->getId());
        $payment->save();

        $liqpay = LiqPay::Constr();

        $data = [
            'version' => '3',
            'amount' => $plan->getPlanValue(),
            'currency' => 'USD',
            'description' => $plan->getPlanName(),
            'order_id' => $payment->getPaymentId(),
            'type' => 'buy',
            'sandbox' => 0,
            'subscribe' => 1,
            'subscribe_date_start' => 'now',
            'subscribe_periodicity' => $plan->getPlanPeriod(),
            'server_url' => 'https://api.myownradio.biz/api/v3/payment'
        ];

        $html = $liqpay->cnb_hyperlink($data);

        return $html;

    }
} 