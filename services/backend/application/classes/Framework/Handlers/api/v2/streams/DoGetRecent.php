<?php

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetRecent implements Controller {
    public function doGet(JsonResponse $response, Streams $streams, HttpGet $get) {

        $offset = $get->getParameter("offset")->getOrElse(0);
        $limit = $get->getParameter("limit")->getOrElse(50);

        $response->setData([
            "streams"  => $streams->getRecentlyUpdated($offset, $limit),
        ]);
    }
}
