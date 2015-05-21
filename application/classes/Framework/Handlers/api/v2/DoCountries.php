<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 31.01.15
 * Time: 20:13
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Services\DB\DBQuery;
use Framework\Services\JsonResponse;

class DoCountries implements Controller {
    public function doGet(JsonResponse $response, DBQuery $query) {
        $countries = $query->selectFrom("mor_countries")->fetchAll();
        $response->setData($countries);
    }
} 