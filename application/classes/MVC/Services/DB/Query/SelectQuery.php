<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.12.14
 * Time: 10:54
 */

namespace MVC\Services\DB\Query;


use PDO;

class SelectQuery extends BaseQuery implements QueryBuilder {

    use WhereSection, SelectSection;


    protected $groups = [];

    private $leftJoin = [];

    public function __construct($tableName) {
        $this->tableName = $tableName;
    }


    // Left join builder section

    public function leftJoin($table, $on) {

        $this->leftJoin[] = [$table, $on];

        return $this;

    }


    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit) {
        $this->limit = intval($limit);
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset($offset) {
        $this->offset = intval($offset);
        return $this;
    }

    // Implements

    public function getQuery(PDO $pdo) {

        $query = [];

        $query[] = "SELECT";
        $query[] = $this->buildSelect();
        $query[] = "FROM " . $this->tableName;
        $query[] = $this->buildLeftJoins();
        $query[] = $this->buildWheres($pdo);
        $query[] = $this->buildGroupBy();
        $query[] = $this->buildOrderBy();
        $query[] = $this->buildLimits();

        return implode(" ", $query);

    }

    private function buildLeftJoins() {

        $build = [];

        foreach($this->leftJoin as $join) {
            $build[] = "LEFT JOIN " . $join[0] . " ON " . $join[1];
        }

        return implode(" ", $build);

    }


    protected function buildGroupBy() {

        if (count($this->groups) > 0) {
            return "GROUP BY " . implode(", ", $this->groups);
        } else {
            return "";
        }

    }


    /**
     * @param string $column
     * @return $this
     */
    public function addGroupBy($column) {
        $this->groups[] = $column;
        return $this;
    }

}