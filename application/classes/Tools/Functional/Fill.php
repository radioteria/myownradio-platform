<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.06.2015
 * Time: 15:11
 */

namespace Tools\Functional;


class Fill extends Sequence {
    /**
     * @param int $size
     * @param $value
     */
    public function __construct($size, $value) {
        parent::__construct(array_fill(0, $size, $value));
    }

}