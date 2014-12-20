<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 10:08
 */

namespace Framework\Services\DB\Query;


use PDO;

class InsertQuery extends BaseQuery implements QueryBuilder {

    protected $names = [];

    /**
     * @param $tableName
     */
    function __construct($tableName) {
        $this->tableName = $tableName;
    }

    public function values() {
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->hashValues(func_get_arg(0));
        } elseif (func_num_args() == 2 && is_string(func_get_arg(0))) {
            $this->singleValue(func_get_arg(0), func_get_arg(1));
        }
        return $this;
    }

    private function singleValue($key, $value) {
        $this->names[] = $key;
        $this->parameters["INSERT"][] = $value;
    }

    private function hashValues(array $hashMap) {
        foreach($hashMap as $key=>$value) {
            $this->singleValue($key, $value);
        }
    }

    protected function groupNames() {

        return implode(",", $this->names);

    }

    public function getQuery(PDO $pdo) {

        $query = [];

        $query[] = "INSERT INTO";
        $query[] = $this->tableName;
        $query[] = "(" . $this->groupNames() . ")";
        $query[] = "VALUES";
        $query[] = "(" . $this->repeat('?', count($this->parameters["INSERT"]), ',') . ")";

        return implode(" ", $query);

    }

}