<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 10:47
 */

namespace Framework\Handlers\api\v3;


use Framework\ControllerImpl;
use Framework\Models\PaymentModel;
use Framework\Services\HttpGet;
use Framework\View\Errors\View400Exception;
use Framework\View\Errors\View500Exception;

class DoAcquire extends ControllerImpl {
    public function doGet(HttpGet $get) {

        $plan_id = $get->getParameter("plan_id", FILTER_VALIDATE_INT)
            ->getOrElseThrow(View400Exception::getClass());


        if ($plan_id < 2 || $plan_id > 5) {
            throw new View400Exception();
        }

        $html = PaymentModel::createOrder($plan_id);

        http_response_code(302);

        header("Location: $html");

    }
} 