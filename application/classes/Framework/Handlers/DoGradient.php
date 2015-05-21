<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Business\Validator\Validator;
use Framework\Controller;

class DoGradient implements Controller {

    public function doGet() {
        $v = new Validator(2);
        echo $v->isExistsInIterator([1,2,3])->run();
    }

    private function getGenerator() {
        echo "Init...";
        yield 1;
        yield 2;
        yield 3;
        echo "Done...";
    }

} 