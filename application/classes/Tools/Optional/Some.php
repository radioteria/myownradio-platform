<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.06.2015
 * Time: 13:48
 */

namespace Tools\Optional;


class Some extends Option {

    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function isEmpty() {
        return false;
    }

    public function get() {
        return $this->value;
    }

    public function __toString() {
        return "Some(" . $this->value . ")";
    }

}
