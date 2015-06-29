<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 13:51
 */

namespace Framework\Handlers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\Http\HttpPost;
use Objects\AccountPlan;

class DoPromoCode implements Controller {
    public function doPost(HttpPost $post, AuthUserModel $user, DBQuery $dbq) {
        $promoCode = $post->getOrError("code");
        $planId = $dbq->selectFrom("mor_promo_codes")
            ->select("plan_id")
            ->where("promo_code", $promoCode)
            ->where("expires > UNIX_TIMESTAMP(NOW())")
            ->where("use_left > 0")
            ->fetchOneColumn()
            ->orThrow(ControllerException::tr("PROMO_CODE_INCORRECT"));
        /** @var AccountPlan $newPlan */
        $newPlan = AccountPlan::getByID($planId)
            ->orThrow(ControllerException::of("This plan is not available"));
        $user->changeAccountPlan($newPlan, "Promo Code - " . $promoCode);

        $dbq->updateTable("mor_promo_codes")
            ->set("use_left = use_left - 1")
            ->where("promo_code", $promoCode)
            ->update();

        return $newPlan->getPlanName();
    }
} 