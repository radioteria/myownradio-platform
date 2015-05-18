<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Controller;
use Framework\Services\Generator;

class DoGradient implements Controller {
    public function doGet() {
        foreach(Generator::generate() as $val) {
            echo $val;
        }
    }


} 