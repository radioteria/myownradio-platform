<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 13:08
 */

namespace Framework\Services\DB\Query;


use PDO;
use Tools\Lang;

class UpdateQuery extends BaseQuery implements QueryBuilder {

    use WhereSection;

    private $sets = [];
    private $setsSingle = [];

    function __construct($tableName, $key = null, $value = null) {
        $this->tableName = $tableName;
        if (!Lang::isNull($key, $value)) {
            $this->where($key, $value);
        }
    }

    public function getQuery(PDO $pdo) {

        $query = [];

        $query[] = "UPDATE " . $this->tableName;
        $query[] = $this->buildSets();
        $query[] = $this->buildWheres($pdo);
        $query[] = $this->buildOrderBy();
        $query[] = $this->buildLimits();

        return implode(" ", $query);

    }

    private function setPair($column, $value) {

        $this->parameters["SET"][] = $value;
        $this->sets[] = $column;

        return $this;

    }

    private function setPairs(array $sets) {
        foreach ($sets as $key => $value) {
            $this->setPair($key, $value);
        }
    }

    private function setSingle($expression) {
        $this->setsSingle[] = $expression;
    }

    public function set() {

        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->setPairs(func_get_arg(0));
        } elseif (func_num_args() == 2 && is_string(func_get_arg(0))) {
            $this->setPair(func_get_arg(0), func_get_arg(1));
        } else if (func_num_args() == 1 && is_string(func_get_arg(0))) {
            $this->setSingle(func_get_arg(0));
        }
        return $this;
    }

    public function buildSets() {

        $build = [];

        foreach ($this->sets as $set) {
            $build[] = $set . "=?";
        }

        foreach ($this->setsSingle as $set) {
            $build[] = $set;
        }

        return "SET " . implode(",", $build);

    }

}