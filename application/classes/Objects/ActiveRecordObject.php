<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 12:04
 */

namespace Objects;


use Framework\MicroORM;
use JsonSerializable;
use Tools\Optional;
use Tools\Singleton;

abstract class ActiveRecordObject implements JsonSerializable {

    /**
     * @return mixed
     */
    public function save() {
        return MicroORM::getInstance()->saveObject($this);
    }

    /**
     * @return void
     */
    public function delete() {
        MicroORM::getInstance()->deleteObject($this);
    }

    /**
     * @param array $data
     * @return static
     */
    public static function getByData(array $data) {
        return MicroORM::getInstance()->getObjectByData(get_called_class(), $data);
    }

    /**
     * @param int $id
     * @return Optional
     */
    public static function getByID($id) {
        return MicroORM::getInstance()->getObjectByID(get_called_class(), $id);
    }

    /**
     * @param string $filter
     * @param array $args
     * @return Optional
     */
    public static function getByFilter($filter, array $args = null) {
        return MicroORM::getInstance()->getObjectByFilter(get_called_class(), $filter, $args);
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return static[]
     */
    public static function getList($limit = null, $offset = null) {
        return MicroORM::getInstance()->getListOfObjects(get_called_class(), $limit, $offset);
    }

    /**
     * @param $filter
     * @param null|array $args
     * @param null $limit
     * @param null $offset
     * @return static[]
     */
    public static function getFilteredList($filter, array $args = null, $limit = null, $offset = null) {
        return MicroORM::getInstance()->getFilteredListOfObjects(get_called_class(), $filter, $args, $limit, $offset);
    }

    /**
     * @return string
     */
    public function __toString() {
        return json_encode($this->exportArray());
    }

    /**
     * @return array
     */
    public function exportArray() {
        $object = [];
        $reflection = new \ReflectionClass($this);
        foreach($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $object[$property->getName()] = $property->getValue($this);
        }
        return $object;
    }

    /**
     * @return $this
     */
    public function cloneObject() {
        return MicroORM::getInstance()->cloneObject($this);
    }

    public function jsonSerialize() {
        $data = [];
        $prefix = "get";
        $reflection = new \ReflectionClass($this);
        foreach($reflection->getMethods() as $method) {
            if ($method->isStatic()) continue;
            if (strpos($method->getName(), $prefix, 0) === 0) {
                $suffix = $this->toUnderscore(substr($method->getName(), strlen($prefix)));
                $data[$suffix] = $method->invoke($this);
            }
        }
        return $data;
    }

    private function toUnderscore($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

} 