<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 18.12.14
 * Time: 21:22
 */

namespace Framework\Services\DB;


use Framework\Services\Database;
use Framework\Services\DB\Query\QueryBuilder;
use Tools\Singleton;
use Tools\SingletonInterface;
use Traversable;

class DBQueryPool implements \IteratorAggregate, \Countable, SingletonInterface {

    use Singleton;

    private $queryPool = [];

    /**
     * @param QueryBuilder $query
     */
    public function put(QueryBuilder $query) {
        $this->queryPool[] = $query;
    }

    /**
     * @param $offset
     * @return QueryBuilder
     */
    public function get($offset) {
        return $this->queryPool[$offset];
    }

    /**
     * @return QueryBuilder
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

    public function execute() {
        Database::doInConnection(function (Database $db) {
            $db->beginTransaction();
            while ($query = $this->shift()) {
                $db->executeUpdate($query);
            }
            $db->commit();
        });
    }
}