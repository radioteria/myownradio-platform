<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 13:04
 */

namespace MVC\Services\DB\Query;


trait SelectSection {

    protected $selects = [];

    // Select builder section

    public function select($column) {

        if (func_num_args() == 1 && is_string(func_get_arg(0))) {
            $this->addSelect(func_get_arg(0));
        } elseif (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->addSelectArray(func_get_arg(0));
        } else {
            $this->addSelectArray(func_get_args());
        }

        return $this;

    }

    private function addSelectArray(array $array) {
        foreach($array as $column) {
            $this->addSelect($column);
        }
    }

    private function addSelect($column) {
        $this->selects[] = $column;
    }

    public function selectAlias($column, $alias) {
        $this->selects[] = [$column, $alias];
        return $this;
    }

    private function selectAll() {
        $this->selects[] = "*";
    }

    // Builders

    private function buildSelect() {

        $build = [];

        foreach ($this->selects as $select) {
            $build[] = is_array($select) ? $select[0] . " AS " . $select[1] : $select;
        }

        return implode(",", $build);

    }



} 