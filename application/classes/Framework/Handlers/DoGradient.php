<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Context;
use Framework\Controller;
use Framework\FileServer\FSFile;
use Framework\Services\Annotations\Annotation;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\JsonResponse;
use Framework\Services\Test\Test;
use Objects\FileServer\FileServerFile;

class DoGradient implements Controller {
    public function doGet() {
        $test = new Test();
        $test->start();
    }


} 