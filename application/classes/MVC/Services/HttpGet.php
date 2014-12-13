<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 17:50
 */

namespace controller\services;

use Tools\Optional;
use Tools\Singleton;

class HttpGet {
    use Singleton;

    public function getParameter($key) {
        return Optional::ofNull(FILTER_INPUT(INPUT_GET, $key));
    }
} 