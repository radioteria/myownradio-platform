<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 17.12.14
 * Time: 10:53
 */

namespace Framework\Services\DB;


use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\DB\Query\InsertQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\DB\Query\UpdateQuery;
use Framework\Services\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class DBQuery implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param $tableName
     * @return InsertQuery
     */
    public function into($tableName) {
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