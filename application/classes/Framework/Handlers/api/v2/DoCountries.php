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

class DoCountries implements Controller {
    public function doGet(DBQuery $query) {
        return $query->selectFrom("mor_countries")->fetchAll();
    }
} 