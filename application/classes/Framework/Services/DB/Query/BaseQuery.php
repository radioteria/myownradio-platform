<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 10:08
 */

namespace Framework\Services\DB\Query;


use Framework\Services\Database;
use PDO;
use Tools\Optional;

abstract class BaseQuery implements \Countable {

    protected $tableName;

    protected $orders = [];

    protected $parameters = [
        "SET" => [],
        "WHERE" => [],
        "INSERT" => [],
        "UPDATE" => []
    ];

    protected $limit = null;
    protected $offset = null;

    protected function quoteColumnName($column) {
        return "`" . $column . "`";
    }

    protected function repeat($char, $count, $glue = "") {
        $chars = [];
        for ($i = 0; $i < $count; $i++) {
            $chars[] = $char;
        }
        return implode($glue, $chars);
    }

    protected function quote(PDO $pdo, array $values) {
        $result = [];
        foreach ($values as $value) {
            $result[] = $pdo->quote($value);
        }
        return $result;
    }

    public function getParameters() {
        return array_merge($this->parameters["INSERT"], $this->parameters["SET"], $this->parameters["WHERE"]);
    }

    public function orderBy($column) {
        if ($column === null) {
            $this->orders = [];
        } else {
            $this->orders[] = $column;
        }
        return $this;
    }


    public function buildLimits() {

        if (is_numeric($this->limit) && is_null($this->offset)) {
            return "LIMIT " . $this->limit;
        } else if (is_numeric($this->limit) && is_numeric($this->offset)) {
            return "LIMIT " . $this->offset . "," . $this->limit;
        } else {
            return "";
        }

    }


    protected function buildOrderBy() {

        if (count($this->orders) > 0) {
            return "ORDER BY " . implode(", ", $this->orders);
        } else {
            return "";
        }

    }

    /* Fetchers shortcuts */

    /**
     * @return Optional
     */
    public function fetchOneRow() {
        return Database::doInConnection(function (Database $db) {
            $query = clone $this;
            $query->limit(1);
            return $db->fetchOneRow($query);
        });
    }

    /**
     * @param int $column
     * @return Optional
     */
    public function fetchOneColumn($column = 0) {
        return Database::doInConnection(function (Database $db) use (&$column) {
            $query = clone $this;
            $query->limit(1);
            return $db->fetchOneColumn($query, null, $column);
        });
    }

    /**
     * @param string|null $key
     * @param callable $callback
     * @return array
     */
    public function fetchAll($key = null, callable $callback = null) {
        return Database::doInConnection(function (Database $db) use (&$key, &$callback) {
            return $db->fetchAll($this, null, $key, $callback);
        });
    }

    /**
     * @param $className
     * @param array $ctor_args
     * @return Optional
     */
    public function fetchObject($className, array $ctor_args = []) {
        return Database::doInConnection(function (Database $db) use (&$className, &$ctor_args) {
            $query = clone $this;
            $query->limit(1);
            return $db->fetchOneObject($query, null, $className, $ctor_args);
        });
    }

    /**
     * @param $className
     * @param array $ctor_args
     * @return Object[]
     */
    public function fetchAllObjects($className, array $ctor_args = []) {
        return Database::doInConnection(function (Database $db) use (&$className, &$ctor_args) {
            return $db->fetchAllObjects($this, null, $className, $ctor_args);
        });
    }

    /**
     * @param callable $callable
     */
    public function eachRow(callable $callable) {
        Database::doInConnection(function (Database $db) use (&$callable) {
            //$db->beginTransaction();
            $db->eachRow($this, null, $callable);
            //$db->commit();
        });
    }

    /**
     * @return mixed
     */
    public function update() {
        return Database::doInConnection(function (Database $db) {
            return $db->executeUpdate($this, null);
        });
    }

    /**
     * @return mixed
     */
    public function executeInsert() {
        return Database::doInConnection(function (Database $db) {
            return $db->executeInsert($this, null);
        });
    }

    /**
     * @return int
     */
    public function count() {
        return Database::doInConnection(function (Database $db) use (&$className, &$ctor_args) {
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
        foreach ($chunks as $chunk) {
            call_user_func($callback, $chunk);
        }
    }

} 