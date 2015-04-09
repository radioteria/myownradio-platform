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
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Locale\I18n;
use Objects\AccountPlan;

class DoPromoCode implements Controller {
    public function doPost(HttpPost $post, JsonResponse $response, AuthUserModel $user, DBQuery $dbq) {
        $promoCode = $post->getRequired("code");
        $planId = $dbq->selectFrom("mor_promo_codes")->select("plan_id")->where("promo_code", $promoCode)
            ->where("expires > UNIX_TIMESTAMP(NOW())")
            ->where("use_left > 0")
            ->fetchOneColumn()
            ->getOrElseThrow(ControllerException::of(I18n::tr("PROMO_CODE_INCORRECT")));
        /** @var AccountPlan $newPlan */
        $newPlan = AccountPlan::getByID($planId)->getOrElseThrow(ControllerException::of("This plan is not available"));
        $user->changeAccountPlan($newPlan, "Promo Code - " . $promoCode);

        $dbq->updateTable("mor_promo_codes")->set("use_left = use_left - 1")->where("promo_code", $promoCode)->update();

        $response->setData($newPlan->getPlanName());
    }
} 