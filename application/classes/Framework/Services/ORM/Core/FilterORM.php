<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 15:43
 */

namespace Framework\Services\ORM\Core;


use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\ORM\Exceptions\ORMException;

abstract class FilterORM {

    /**
     * @param array $config
     * @return SelectQuery
     */
    protected function createBaseSelectRequest(array $config) {
        $query = DBQuery::getInstance()->selectFrom($config["@table"])
            ->select($config["@table"] . ".*");
        return $query;
    }

    /**
     * @param SelectQuery $query
     * @param array $config
     */
    protected function applyInnerJoin(&$query, array $config) {
        if (isset($config["@innerJoin"], $config["@on"])) {
            $query->innerJoin($config["@innerJoin"], $config["@on"]);
            $query->select($config["@innerJoin"] . ".*");
        }
    }

    /**
     * @param SelectQuery $query
     * @param string $filter
     * @param array|null $config
     * @param array|null $args
     * @throws ORMException
     */
    protected function applyFilter(SelectQuery &$query, $filter, array $config, array $args = null) {

        if (isset($config["@do_" . $filter])) {
            $query->where($config["@do_" . $filter], $args);
        } else {
            $this->applyCustomFilter($query, $filter, $args);
        }

    }

    /**
     * @param SelectQuery $query
     * @param $filter
     * @param array $args
     */
    protected function applyCustomFilter(SelectQuery &$query, $filter, array $args = null) {

        $query->where($filter, $args);

    }

    /**
     * @param SelectQuery $query
     * @param $config
     * @param $id
     */
    protected function applyKey(&$query, $config, $id) {

        $query->where($config["@key"], $id);

    }

} 