<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 14:43
 */

namespace MVC\Services\DB\Query;


use PDO;
use Tools\Lang;

class DeleteQuery extends BaseQuery implements QueryBuilder {

    use WhereSection;

    function __construct($tableName, $key = null, $value = null) {
        $this->tableName = $tableName;
        if (!Lang::isNull($key, $value)) {
            $this->where($key, $value);
        }
    }


    public function getQuery(PDO $pdo) {
        $build = [];

        $build[] = "DELETE FROM";
        $build[] = $this->tableName;
        $build[] = $this->buildWheres($pdo);
        $query[] = $this->buildOrderBy();
        $query[] = $this->buildLimits();

        return implode(" ", $build);
    }
}