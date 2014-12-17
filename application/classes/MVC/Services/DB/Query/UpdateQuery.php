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

    public function set($column, $value) {
        $this->parameters["SET"][] = $value;
        $this->sets[] = $column;
    }

    public function buildSets() {

        $build = [];

        foreach($this->sets as $set) {
            $build[] = $set . " = ?";
        }

        return "SET " . implode(",", $build);

    }
}