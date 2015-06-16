<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.06.2015
 * Time: 14:26
 */

namespace Tools\Functional;


class Range extends Sequence {
    /**
     * @param int $start
     * @param int $end
     * @param int $step
     */
    public function __construct($start, $end, $step = 1) {
        parent::__construct(range($start, $end, $step));
    }
}
