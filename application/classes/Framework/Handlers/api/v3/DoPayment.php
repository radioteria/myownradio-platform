<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 9:53
 */

namespace Framework\Handlers\api\v3;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Models\UserModel;
use Framework\Services\HttpPost;
use LiqPay;
use Objects\AccountPlan;
use Objects\PaymentOrder;

class DoPayment extends ControllerImpl {
    public function doPost(HttpPost $post) {
        $data = $post->getRequired("data");
        $signature = $post->getRequired("signature");

        // Check signature
        if (base64_encode(sha1(LiqPay::$private_key . $data . LiqPay::$private_key, 1)) !== $signature) {
            error_log("Wrong signature");
            throw new ControllerException("Wrong signature!");
        }

        $json = base64_decode($data);
        $params = json_decode($json, true);

        if ($params["status"] == "success" || $params["status"] == "sandbox") {
            /** @var PaymentOrder $order */
            $order = PaymentOrder::getById($params["order_id"])
                ->getOrElseThrow(new ControllerException("Wrong order id"));

            $plan = AccountPlan::getById($order->getPlanId())
                ->getOrElseThrow(new ControllerException("Wrong account plan"));

            $user = UserModel::getInstance($order->getUserId());
            $user->changeAccountPlan($plan, "LIQPAY", $json);

            $order->setStatus(1);
            $order->save();

            echo "OK";
        }



    }
} 