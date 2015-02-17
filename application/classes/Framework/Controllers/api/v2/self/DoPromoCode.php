<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 13:51
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Objects\AccountPlan;

class DoPromoCode implements Controller {
    public function doPost(HttpPost $post, JsonResponse $response, AuthUserModel $user, DBQuery $dbq) {
        $promoCode = $post->getRequired("code");
        $planId = $dbq->selectFrom("mor_promo_codes")->select("plan_id")->where("promo_code", $promoCode)
            ->where("expires > UNIX_TIMESTAMP(NOW())")->fetchOneColumn()
            ->getOrElseThrow(ControllerException::of("You are entered incorrect promo code. Please try another one."));
        $newPlan = AccountPlan::getByID($planId)->getOrElseThrow(ControllerException::of("This plan is not available"));
        $user->changeAccountPlan($newPlan, "Promo Code");
    }
} 