<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use MVC\Controller;
use MVC\Services\Database;
use MVC\Services\DB\Query\InsertQuery;
use MVC\Services\JsonResponse;

class DoTest extends Controller {
    public function doGet(JsonResponse $response) {

        $pdo = Database::getInstance()->getPDO();

        $query = new InsertQuery("r_streams");
        $query->values("access", 1);

        $response->setMessage($query->getQuery($pdo));
        $response->setData($query->getParameters());

    }
} 