<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:26
 */

namespace controller\services;

use Tools\Optional;
use Tools\Singleton;

class HttpPost {
    use Singleton;

    public function getParameter($key) {
        return Optional::ofNull(FILTER_INPUT(INPUT_POST, $key));
    }
} 