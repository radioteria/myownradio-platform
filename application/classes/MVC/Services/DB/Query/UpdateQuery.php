<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.12.14
 * Time: 13:08
 */

namespace MVC\Services\DB\Query;


use PDO;

class UpdateQuery extends BaseQuery implements QueryBuilder {

    use WhereSection;

    private $sets = [];

    function __construct($tableName) {
        $this->tableName = $tableName;
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

    public function set() {
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->setPairs(func_get_arg(0));
        } elseif (func_num_args() == 2 && is_string(func_get_arg(0)) && is_string(func_get_arg(1))) {
            $this->setPair(func_get_arg(0), func_get_arg(1));
        }
        return $this;
    }

    public function buildSets() {

        $build = [];

        foreach($this->sets as $set) {
            $build[] = $set . " = ?";
        }

        return "SET " . implode(",", $build);

    }
}