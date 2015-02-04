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

class DoIndex implements Controller {
    public function doGet() {
        /** @var ActiveRecordCollection $streams */
        $streams = Stream::getList();

    }
} 