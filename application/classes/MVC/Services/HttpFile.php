<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:27
 */

namespace MVC\Services;

use Tools\Optional;
use Tools\Singleton;

class HttpFile {
    use Singleton, Injectable;

    public function getFile($file) {
        return Optional::ofEmpty(@$_FILES[$file]);
    }
} 