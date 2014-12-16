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

    public function each(callable $callback) {
        foreach ($_FILES as $file) {
            call_user_func($callback, $file);
        }
    }
} 