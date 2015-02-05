<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 29.12.14
 * Time: 16:59
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\ORM\EntityUtils\ActiveRecordCollection;
use Objects\Stream;
use Objects\Track;
use Tools\Common;

class DoIndex implements Controller {
    public function doGet() {
        $query = "chill-out@test";

        print_r(Common::searchQueryFilter($query));
    }
} 