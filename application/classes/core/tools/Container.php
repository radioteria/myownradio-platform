<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.12.14
 * Time: 22:06
 */

class Container {
    private $values;

    public function __construct() {
        $this->values = func_get_args();
    }

    public function get() {
        foreach ($this->values as $value) {
            if(!empty($value)) {
                return $value;
            }
        }
        return null;
    }

} 