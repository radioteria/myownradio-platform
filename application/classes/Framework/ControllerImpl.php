<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 10.04.15
 * Time: 9:50
 */

namespace Framework;


abstract class ControllerImpl implements Controller {
    /**
     * @return string
     */
    public static function getClass() {
        return get_called_class();
    }
} 