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
use MVC\Services\JsonResponse;

class DoTest extends Controller {
    public function doGet(JsonResponse $response) {

        $track = Database::doInTransaction(function (Database $db) {

            return $db->fetchOneRow("SELECT 1")->getOrElse("No track");

        });

        $response->setData($track);

    }
} 