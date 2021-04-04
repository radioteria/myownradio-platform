<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 9:53
 */

namespace Framework\Handlers\api\v3;


use Framework\ControllerImpl;
use Framework\Models\PaymentModel;
use Framework\Services\HttpPost;
use Framework\View\Errors\View400Exception;
use LiqPay;

class DoPayment extends ControllerImpl {
    public function doPost(HttpPost $post) {

        $data = $post->getParameter("data")
            ->getOrElseThrow(View400Exception::getClass());
        $signature = $post->getParameter("signature")
            ->getOrElseThrow(View400Exception::getClass());

        // Check signature
        if (base64_encode(sha1(LiqPay::$private_key . $data . LiqPay::$private_key, 1)) !== $signature) {
            throw new View400Exception("Wrong signature!");
        }

        $json = base64_decode($data);
        $params = json_decode($json, true);

        if ($params["status"] == "success" || $params["status"] == "sandbox") {

            PaymentModel::confirmOrder($params["order_id"], $json);

        }

    }
} 