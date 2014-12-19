<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 12:04
 */

namespace Objects;


use MVC\MicroORM;
use Tools\Optional;

trait ActiveRecordObject {

    public function save() {
        return MicroORM::getInstance()->saveObject($this);
    }

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
        return MicroORM::getInstance()->getListOfObjects(get_called_class(), null, null, $limit, $offset);
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

} 