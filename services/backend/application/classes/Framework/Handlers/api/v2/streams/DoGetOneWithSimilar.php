<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.02.15
 * Time: 12:37
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetOneWithSimilar implements Controller {

    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams, DBQuery $dbq) {

        $id = $get->getRequired("stream_id");

        $one = $streams->getOneStream($id);

        if (is_array($one)) {
            $similar = $streams->getSimilarTo($id);
        } else {
            $similar = [];
        }

        if (is_array($one)) {
            $comments = $dbq->selectFrom("mor_comment")
                ->where("comment_stream", $id)->fetchAll();
        } else {
            $comments = [];
        }

        $response->setData([
            "stream"  => $one,
            "similar" => $similar,
            "comments" => $comments
        ]);

    }

} 