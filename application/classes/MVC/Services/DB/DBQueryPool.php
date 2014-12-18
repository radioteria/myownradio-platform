<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 18.12.14
 * Time: 21:22
 */

namespace MVC\Services\DB;


use Traversable;

class DBQueryPool implements \IteratorAggregate, \Countable {

    private $queryPool = [];

    /**
     * @param DBQueryWrapper $query
     */
    public function put(DBQueryWrapper $query) {
        $this->queryPool[] = $query;
    }

    /**
     * @param $offset
     * @return DBQueryWrapper
     */
    public function get($offset) {
        return $this->queryPool[$offset];
    }

    /**
     * @return DBQueryWrapper
     */
    public function shift() {
        return array_shift($this->queryPool);
    }

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator() {
        return new \ArrayIterator($this->queryPool);
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->queryPool);
    }
}