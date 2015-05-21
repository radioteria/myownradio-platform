<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.01.15
 * Time: 11:00
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Services\DB\DBQuery;
use Framework\Services\JsonResponse;

class DoCategories implements Controller {
    public function doGet(DBQuery $query, JsonResponse $response) {
        $categories = $query->selectFrom("r_categories")->fetchAll();
        $response->setData($categories);
    }
} 