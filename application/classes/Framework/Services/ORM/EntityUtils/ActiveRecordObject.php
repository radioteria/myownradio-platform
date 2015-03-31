<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 12:04
 */

namespace Framework\Services\ORM\EntityUtils;


use Framework\Services\ORM\Core\MicroORM;
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
     * @param null $order
     * @return static[]
     */
    public static function getList($limit = null, $offset = null, $order = null) {
        return MicroORM::getInstance()->getListOfObjects(get_called_class(), $limit, $offset, $order);
    }

    /**
     * @param $filter
     * @param null|array $args
     * @param null $limit
     * @param null $offset
     * @param null $order
     * @return static[]
     */
    public static function getListByFilter($filter, array $args = null, $limit = null, $offset = null, $order = null) {
        return MicroORM::getInstance()->getFilteredListOfObjects(get_called_class(), $filter, $args, $limit, $offset, $order);
    }

    /**
     * @internal param $reflection
     * @return $this
     */
    public function cloneObject() {
        return MicroORM::getInstance()->cloneObject($this);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize() {
        $data = [];
        $prefix = "get";
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getMethods() as $method) {
            if ($method->isStatic()) continue;
            if (strpos($method->getName(), $prefix, 0) === 0) {
                $suffix = camelToUnderscore(substr($method->getName(), strlen($prefix)));
                $data[$suffix] = $method->invoke($this);
            }
        }
        return $data;
    }

    public static function getClass() {
        return self;
    }

} 