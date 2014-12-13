<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 23:48
 */

namespace MVC\Exceptions;


class NotImplementedException extends \Exception {
    public function __construct() {
        parent::__construct();
    }
} 