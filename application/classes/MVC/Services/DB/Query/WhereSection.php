<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.12.14
 * Time: 13:02
 */

namespace MVC\Services\DB\Query;


use PDO;

trait WhereSection {

    protected $wheres = [];

    // Where section

    public function where($clause) {
        if (func_num_args() == 2 && is_array(func_get_arg(1))) {
            $this->whereArray(func_get_arg(0), func_get_arg(1));
        } elseif (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->whereHashMap(func_get_arg(0));
        } elseif (func_num_args() == 2) {
            $this->whereSimple(func_get_arg(0), func_get_arg(1));
        } elseif (func_num_args() == 1) {
            $this->whereSimple($clause);
        }
        return $this;
    }

    private function whereSimple($column, $value = null) {
        if (is_null($value)) {
            $this->wheres[] = $column;
        } else {
            $this->parameters["WHERE"][] = $value;
            $this->wheres[] = [$column, "?"];
        }
    }

    private function whereParameters($clause, array $parameters) {
        foreach ($parameters as $key=>$parameter) {
            if (is_numeric($key)) {
                $this->parameters["WHERE"][] = $parameter;
                $this->wheres[] = $clause;
            } else if (is_string($key)) {
                $this->parameters["WHERE"][$key] = $parameter;
                $this->wheres[] = $clause;
            }
        }
    }

    private function whereArray($column, array $values) {
        if (preg_match("~(\\?)|(\\:\\w+)~m", $column)) {
            $this->whereParameters($column, $values);
        } else {
            $this->wheres[] = [$column, $values];
        }
    }

    private function whereHashMap(array $map) {
        foreach($map as $key=>$value) {
            $this->whereSimple($key, $value);
        }
    }


    private function buildWheres(PDO $pdo) {

        $build = [];

        foreach($this->wheres as $where) {
            if (count($where) == 1 && is_string($where)) {
                $build[] = $where;
            } else if (count($where) == 2 && is_array($where[1])) {
                $build[] = $where[0] . " IN (" . $this->quote($pdo, $where[1]) . ")";
            } else {
                $build[] = $where[0] . " = " . $where[1];
            }
        }

        return "WHERE " . implode(" AND ", $build);

    }


} 