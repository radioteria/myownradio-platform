<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 10:53
 */

namespace MVC\Services\DB;


use MVC\Services\DB\Query\DeleteQuery;
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
     * @param string $key
     * @param mixed $value
     * @return SelectQuery
     */
    public function selectFrom($tableName, $key = null, $value = null) {
        return new SelectQuery($tableName, $key, $value);
    }

    /**
     * @param $tableName
     * @param string $key
     * @param mixed $value
     * @return UpdateQuery
     */
    public function updateTable($tableName, $key = null, $value = null) {
        return new UpdateQuery($tableName, $key, $value);
    }

    /**
     * @param $tableName
     * @param string $key
     * @param mixed $value
     * @return DeleteQuery
     */
    public function deleteFrom($tableName, $key = null, $value = null) {
        return new DeleteQuery($tableName, $key, $value);
    }

} 