<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 12:49
 */

namespace Framework;


trait ObjectTrait {
    /**
     * @return string
     */
    public static function getClass() {
        return get_called_class();
    }
} 