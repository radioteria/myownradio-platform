<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 14:43
 */

namespace MVC\Services\DB\Query;


use PDO;

class DeleteQuery extends BaseQuery implements QueryBuilder {

    use WhereSection;

    function __construct($tableName) {
        $this->tableName = $tableName;
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