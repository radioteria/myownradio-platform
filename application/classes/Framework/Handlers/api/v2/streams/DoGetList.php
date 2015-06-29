<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:08
 */

namespace Framework\Handlers\api\v2\streams;

use Framework\Controller;
use Framework\Services\Http\HttpGet;
use REST\Streams;

class DoGetList implements Controller {

    public function doGet(HttpGet $get, Streams $streams) {

        $filter     = $get->get("filter")->orNull();
        $category   = $get->get("category")->orNull();

        $offset     = $get->get("offset")->orZero();
        $limit      = $get->get("limit")->getOrElse(50);

        return $streams->getStreamListFiltered($filter, $category, $offset, $limit);

    }

}