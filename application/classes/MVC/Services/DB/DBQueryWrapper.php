<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 18.12.14
 * Time: 21:38
 */

namespace MVC\Services\DB;

use MVC\Services\DB\Query\QueryBuilder;

/**
 * Class DBQueryWrapper
 * @package MVC\Services\DB
 *
 * @property QueryBuilder $queryBody
 * @property array $queryParams
 */
class DBQueryWrapper {

    private $queryBody;
    private $queryParams;

    function __construct($queryBody, $queryParams) {
        $this->queryBody = $queryBody;
        $this->queryParams = $queryParams;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBody() {
        return $this->queryBody;
    }

    /**
     * @return array
     */
    public function getQueryParams() {
        return $this->queryParams;
    }


} 