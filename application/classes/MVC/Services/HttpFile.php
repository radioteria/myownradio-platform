<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:27
 */

namespace controller\services;

use Tools\Optional;
use Tools\Singleton;

class HttpFile {
    use Singleton;

    public function getFile($file) {
        return Optional::ofNull(@$_FILES[$file]);
    }
} 