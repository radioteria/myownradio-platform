<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Controller;
use Framework\Services\ORM\Wrapper\Wrapper;

class DoGradient implements Controller {
    public function doGet() {
        $wrapper = new Wrapper();
        echo $wrapper->keyToGetter("user_id");
    }


} 