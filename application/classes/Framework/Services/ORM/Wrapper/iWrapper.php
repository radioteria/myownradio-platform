<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.05.15
 * Time: 12:38
 */

namespace Framework\Services\ORM\Wrapper;


interface iWrapper {
    public function wrap($object_data, $object_class);
} 