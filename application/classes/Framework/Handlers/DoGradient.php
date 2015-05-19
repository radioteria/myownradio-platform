<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Controller;
use Objects\Stream;

class DoGradient implements Controller {
    public function doGet() {
        foreach (Stream::getList() as $stream) {
            echo $stream->getName();
            echo "<br>";
        }
    }


} 