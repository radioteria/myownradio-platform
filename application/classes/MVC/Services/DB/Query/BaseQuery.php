<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.12.14
 * Time: 10:08
 */

namespace MVC\Services\DB\Query;


use PDO;

abstract class BaseQuery {

    protected $tableName;

    protected $orders = [];

    protected $parameters = [
        "SET" => [],
        "WHERE" => [],
        "INSERT" => []
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
        foreach($values as $value) {
            $result = $pdo->quote($value);
        }
        return $result;
    }

    public function getParameters() {
        return array_merge($this->parameters["INSERT"], $this->parameters["SET"], $this->parameters["WHERE"]);
    }

    public function addOrderBy($column) {
        $this->orders[] = $column;
        return $this;
    }


    public function buildLimits() {

        if(is_numeric($this->limit) && is_null($this->offset)) {
            return "LIMIT " . $this->limit;
        } else if(is_numeric($this->limit) && is_numeric($this->offset)) {
            return "LIMIT " . $this->offset . "," .$this->limit;
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


} 