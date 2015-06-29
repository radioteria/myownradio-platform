<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.01.15
 * Time: 18:58
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use REST\Streams;

class DoGetLink implements Controller {
    public function doGet($stream_id, Streams $streams) {
        $stream = $streams->getOneStream($stream_id);
        return array(
            "stream" => $stream
        );
    }
} 