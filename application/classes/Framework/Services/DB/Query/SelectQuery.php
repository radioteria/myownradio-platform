<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 10:54
 */

namespace Framework\Services\DB\Query;


use Framework\Exceptions\ControllerException;
use Framework\Services\Database;
use PDO;
use Tools\Lang;

class SelectQuery extends BaseQuery implements QueryBuilder, \Countable {

    use WhereSection, SelectSection, HavingSection;


    protected $groups = [];

    private $innerJoin = [];
    private $leftJoin = [];

    public function __construct($tableName, $key = null, $value = null) {
        $this->tableName = $tableName;
        if (!Lang::isNull($key, $value)) {
            $this->where($key, $value);
        }
    }

    /**
     * @return int
     */
    public function count() {
        return Database::doInConnection(function (Database $db) {
            $query = clone $this;
            $query->selectNone()->selCount();
            $query->limit(null);
            $query->offset(null);
            $query->orderBy(null);
            return intval($db->fetchOneColumn($query)->get());
        });
    }

    /**
     * @param $chunk_size
     * @param $callback
     */
    public function chunk($chunk_size, $callback) {
        $items = $this->fetchAll();
        $chunks = array_chunk($items, $chunk_size);
        while ($chunk = array_shift($chunks)) {
            call_user_func($callback, $chunk);
        }
    }

    // Inner join builder section

    public function innerJoin($table, $on) {

        $this->innerJoin[] = [$table, $on];

        return $this;

    }

    // Left join builder section

    public function leftJoin($table, $on) {

        $this->leftJoin[] = [$table, $on];

        return $this;

    }

    /**
     * @param int $limit
     * @throws \Framework\Exceptions\ControllerException
     * @return $this
     */
    public function limit($limit) {
        if ($limit !== null && !is_numeric($limit) && $limit < 0) {
            throw ControllerException::of("Invalid limit");
        }
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @throws \Framework\Exceptions\ControllerException
     * @return $this
     */
    public function offset($offset) {
        if ($offset !== null && !is_numeric($offset) && $offset < 0) {
            throw ControllerException::of("Invalid offset");
        }
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
        $query[] = $this->buildLeftJoins();
        $query[] = $this->buildWheres($pdo);
        $query[] = $this->buildGroupBy();
        $query[] = $this->buildHaving($pdo);
        $query[] = $this->buildOrderBy();
        $query[] = $this->buildLimits();

        return implode(" ", $query);

    }

    private function buildInnerJoins() {

        $build = [];

        foreach ($this->innerJoin as $join) {
            $build[] = "INNER JOIN " . $join[0] . " ON " . $join[1];
        }

        return implode(" ", $build);

    }

    private function buildLeftJoins() {

        $build = [];

        foreach ($this->leftJoin as $join) {
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