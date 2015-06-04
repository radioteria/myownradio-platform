<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:24
 */

namespace Framework\Exceptions;


/**
 * Class UnauthorizedException
 * @package Framework\Exceptions
 * @localized 22.05.2015
 */
class AccessException extends ControllerException {

    function __construct($message = null, $data = null) {
        parent::__construct($message, $data, 0);
    }

}