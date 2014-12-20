<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 10:54
 */

namespace Framework\Services\DB\Query;


use PDO;
use Tools\Lang;

class SelectQuery extends BaseQuery implements QueryBuilder {

    use WhereSection, SelectSection;


    protected $groups = [];

    private $innerJoin = [];

    public function __construct($tableName, $key = null, $value = null) {
        $this->tableName = $tableName;
        if (!Lang::isNull($key, $value)) {
            $this->where($key, $value);
        }
    }


    // Left join builder section

    public function innerJoin($table, $on) {

        $this->innerJoin[] = [$table, $on];

        return $this;

    }


    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    // Implements

    public function getQuery(PDO $pdo) {

        $query = [];

        $query[] = "SELECT";
        $query[] = $this->buildSelect();
        $query[] = "FROM " . $this->tableName;
        $query[] = $this->buildInnerJoins();
        $query[] = $this->buildWheres($pdo);
        $query[] = $this->buildGroupBy();
        $query[] = $this->buildOrderBy();
        $query[] = $this->buildLimits();

        return implode(" ", $query);

    }

    private function buildInnerJoins() {

        $build = [];

        foreach($this->innerJoin as $join) {
            $build[] = "INNER JOIN " . $join[0] . " ON " . $join[1];
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