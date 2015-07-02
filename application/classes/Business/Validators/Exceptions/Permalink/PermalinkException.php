<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:15
 */

namespace Business\Validators\Exceptions\Permalink;


use Business\Validators\Exceptions\ValidationException;

class PermalinkException extends ValidationException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }
}