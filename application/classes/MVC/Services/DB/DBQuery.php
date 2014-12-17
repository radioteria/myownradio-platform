<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.12.14
 * Time: 10:53
 */

namespace MVC\Services\DB;


use MVC\Services\DB\Query\InsertQuery;
use MVC\Services\DB\Query\SelectQuery;
use MVC\Services\DB\Query\UpdateQuery;
use Tools\Singleton;

class DBQuery {

    use Singleton;

    /**
     * @param $tableName
     * @return InsertQuery
     */
    public function insertInto($tableName) {
        return new InsertQuery($tableName);
    }

    /**
     * @param $tableName
     * @return SelectQuery
     */
    public function selectFrom($tableName) {
        return new SelectQuery($tableName);
    }

    /**
     * @param $tableName
     * @return UpdateQuery
     */
    public function updateTable($tableName) {
        return new UpdateQuery($tableName);
    }

} 