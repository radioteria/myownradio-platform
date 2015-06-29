<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:53
 */

namespace Framework\Handlers\api\v2\streams;

use Framework\Controller;
use REST\Streams;

class DoGetOne implements Controller {

    public function doGet($stream_id, Streams $streams) {

        return $streams->getOneStream($stream_id);

    }

} 