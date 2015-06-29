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
use REST\Streams;

class DoGetOneWithSimilar implements Controller {

    public function doGet($stream_id, Streams $streams, DBQuery $dbq) {

        $one = $streams->getOneStream($stream_id);

        if (is_array($one)) {
            $similar = $streams->getSimilarTo($stream_id);
        } else {
            $similar = [];
        }

        if (is_array($one)) {
            $comments = $dbq->selectFrom("mor_comment")
                ->where("comment_stream", $stream_id)->fetchAll();
        } else {
            $comments = [];
        }

        return [
            "stream" => $one,
            "similar" => $similar,
            "comments" => $comments
        ];

    }

} 