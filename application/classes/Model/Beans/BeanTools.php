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

} 