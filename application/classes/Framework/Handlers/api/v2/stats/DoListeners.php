<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 27.03.15
 * Time: 9:13
 */

namespace Framework\Handlers\api\v2\stats;


use Framework\Controller;
use Framework\Services\DB\DBQuery;

class DoListeners implements Controller {
    public function doGet(DBQuery $DBQuery) {
        return count($DBQuery->selectFrom("r_listener")->where("finished IS NULL"));
    }
} 