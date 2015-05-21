<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.05.15
 * Time: 12:40
 */

namespace Framework\Services\ORM\Wrapper;


class Wrapper implements iWrapper {
    public function wrap($object_data, $object_class) {

    }
    private function wrapUsingSetters($object_data, $object_class) {
        $ref = new \ReflectionClass($object_class);
        $instance = $ref->newInstance();
        $setters = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($object_class as $key => $value) {

        }
    }
    private function wrapUsingFieldNames($object_data, $object_class) {

    }
    public function keyToGetter($key) {
        return "get" . preg_replace("/(?:^|\\_)(\\w)/e", "strtoupper('$1')", strtolower($key));
    }
}