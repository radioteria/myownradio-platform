<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 15:43
 */

namespace MVC;


use MVC\Services\DB\DBQuery;

abstract class FilterORM {
    /**
     * @param array $config
     * @return \MVC\Services\DB\Query\SelectQuery
     */
    protected function createBaseSelectRequest(array $config) {
        $query = DBQuery::getInstance()->selectFrom($config["@table"])
            ->select($config["@table"] . ".*");
        return $query;
    }

    /**
     * @param \MVC\Services\DB\Query\SelectQuery $query
     * @param array $config
     */
    protected function applyInnerJoin(&$query, array $config) {
        if (isset($config["@innerJoin"], $config["@on"])) {
            $query->innerJoin($config["@innerJoin"], $config["@on"]);
            $query->select($config["@innerJoin"] . ".*");
        }
    }

    /**
     * @param \MVC\Services\DB\Query\SelectQuery $query
     * @param string $filter
     * @param array $config
     * @param array $args
     */
    protected function applyFilter(&$query, $filter, array $config, array $args = null) {

        if (isset($config["@do_" . $filter])) {
            $query->where($config["@do_" . $filter], $args);
        }
    }

    /**
     * @param \MVC\Services\DB\Query\SelectQuery $query
     * @param $config
     * @param $id
     */
    protected function applyKey(&$query, $config, $id) {

        $query->where($config["@key"], $id);

    }

} 