<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 23.12.14
 * Time: 16:49
 */

namespace Framework\Services\Module;


use Exception;

class ModuleNotFoundException extends \Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

} 