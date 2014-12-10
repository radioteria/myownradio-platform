<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 03.12.14
 * Time: 10:25
 */

// todo: may be unused
class ArrayWorks {
    private $array;

    function __construct($array)
    {
        $this->array = $array;
    }

    public function array_map($callback) {
        $tmp_array = array();
        foreach ($this->array as $node) {
            $tmp_array[] = $callback($node);
        }

        return new ArrayWorks($tmp_array);
    }

    public function array_filter($callback) {
        $tmp_array = array();
        foreach ($this->array as $node) {
            if ($callback($node)) {
                $tmp_array[] = $node;
            }
        }
        return new ArrayWorks($tmp_array);
    }

    public function array_unique() {
        $tmp_array = array();
        foreach ($this->array as $node) {
            if (array_search($node, $tmp_array, true) === false) {
                $tmp_array[] = $node;
            }
        }
        return new ArrayWorks($tmp_array);
    }

    public function array_accumulate($container, $callback) {
        foreach($this->array as $node) {
            $container = $callback($container, $node);
        }
        return $container;
    }

    public function array_implode($glue) {
        return implode($glue, $this->array);
    }

    public function get() {
        return $this->array;
    }
} 