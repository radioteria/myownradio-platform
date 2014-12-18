<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 12:04
 */

namespace Model\Beans;


use MVC\MicroORM;

trait BeanTools {

    public function beanSave() {
        MicroORM::getInstance()->saveObject($this);
    }

    /**
     * @param int $id
     * @return static
     */
    public static function beanLoad($id) {
        return MicroORM::getInstance()->fetchObject(get_called_class(), $id);
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return static[]
     */
    public static function getAll($limit = null, $offset = null) {
        return MicroORM::getInstance()->getListOfObjects(get_called_class(), null, null, $limit, $offset);
    }

    /**
     * @param $filter
     * @param null|array $args
     * @param null $limit
     * @param null $offset
     * @return static[]
     */
    public static function findByFilter($filter, array $args = null, $limit = null, $offset = null) {
        return MicroORM::getInstance()->doActionOnObject(get_called_class(), $filter, $args, $limit, $offset);
    }

    public function __toString() {
        return "Hello";
    }

} 