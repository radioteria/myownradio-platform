<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.05.15
 * Time: 17:46
 */

namespace Framework\Services;


class Generator {
    /**
     * @return \Generator
     */
    public static function generate() {
        for ($a = 0; $a < 10; $a ++) {
            yield $a;
        }
    }
} 