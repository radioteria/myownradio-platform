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
use MVC\Services\DB\DBQuery;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        Database::doInConnection(function (Database $db) use ($response) {

            $query = DBQuery::getInstance()->selectFrom("table")->select("*")
                ->where("id", [1,2,3,4,5]);

            $response->setMessage($query->getQuery($db->getPDO()));

        });

    }

} 