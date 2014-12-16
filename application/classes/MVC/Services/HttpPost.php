<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:26
 */

namespace MVC\Services;

use Tools\Optional;
use Tools\Singleton;

class HttpPost {
    use Singleton, Injectable;

    public function getParameter($key) {
        return Optional::ofEmpty(FILTER_INPUT(INPUT_POST, $key));
    }
} 