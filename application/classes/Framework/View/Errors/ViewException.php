<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.02.15
 * Time: 11:16
 */

namespace Framework\View\Errors;


use Framework\Exceptions\ApplicationException;
use Framework\Template;

abstract class ViewException extends ApplicationException {

    abstract public function render();

} 