<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 10:41
 */

namespace Model\ActiveRecords;

/**
 * Class Test
 * @package Model\ActiveRecords
 *
 * @table user
 *
 * @property int id
 * @property string name
 *
 * @method void save()
 */
class Test {

    private $bank = [];

    public function __set($name, $value) {
        $this->bank[$name] = $value;
    }

    public function __get($name) {
        return @$this->bank[$name];
    }

} 