<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.06.2015
 * Time: 13:50
 */

namespace Tools\Optional;


class None extends Option {

    private static $_instance = null;

    private function __construct() {
    }

    /**
     * @return None
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function isEmpty() {
        return true;
    }

    /**
     * @throws OptionException
     * @return null
     */
    public function get() {
        throw new OptionException("No such element");
    }

    public function __toString() {
        return "None";
    }

}

