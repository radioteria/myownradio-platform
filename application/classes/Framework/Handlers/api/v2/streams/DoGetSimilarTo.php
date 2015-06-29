<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:45
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use REST\Streams;

class DoGetSimilarTo implements Controller {

    public function doGet($stream_id, Streams $streams) {

        return $streams->getSimilarTo($stream_id);

    }

} 