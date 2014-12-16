<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 9:22
 */

namespace Tools;


use Exception;

class FileException extends \Exception {

    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public static function fileNotFound($name) {
        return new self(sprintf("File '%s' not found", $name));
    }

    public static function noAccess($file) {
        return new self(sprintf("File '%s' could not be deleted", $file));
    }

    public static function isNotDir($file) {
        return new self(sprintf("File '%s' is not directory", $file));
    }

    public static function fileExists($file) {
        return new self(sprintf("File '%s' exists", $file));
    }

} 