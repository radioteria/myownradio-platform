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
use Framework\Services\Http\HttpGet;
use Framework\View\Errors\View400Exception;
use Tools\Optional\Filter;

class DoAcquire extends ControllerImpl {
    public function doGet(HttpGet $get) {

        $plan_id = $get->get("plan_id")
            ->filter(Filter::isValidId())
            ->getOrThrow(View400Exception::class);

        $available = [2, 4];

        if (in_array($plan_id, $available)) {
            throw new View400Exception();
        }

        $html = PaymentModel::createOrder($plan_id);

        http_response_code(302);

        header("Location: $html");

    }
} 