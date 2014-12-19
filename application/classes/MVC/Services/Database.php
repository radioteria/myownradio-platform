<?php

namespace MVC\Services;

use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Services\DB\DBQuery;
use MVC\Services\DB\DBQueryPool;
use MVC\Services\DB\DBQueryWrapper;
use MVC\Services\DB\Query\QueryBuilder;
use PDO;
use Tools\Optional;
use Tools\Singleton;

class Database {

    use Singleton, Injectable;

    /** @var PDO $pdo */
    private $pdo;
    private $settings;

    public function __construct() {

        $this->settings = Config::getInstance()->getSection('database')->getOrElse([
            "db_login"    => "root",
            "db_password" => "",
            "db_dsn"      => "mysql:host=localhost;dbname=myownradio"
        ]);

    }

    /**
     * @return $this
     * @throws ApplicationException
     */
    public function connect() {

        try {
            $this->pdo = new PDO($this->settings['db_dsn'],
                $this->settings['db_login'],
                $this->settings['db_password'], [
                    PDO::ATTR_EMULATE_PREPARES  => false,
                    PDO::ATTR_PERSISTENT        => true,
                    PDO::ATTR_AUTOCOMMIT        => true
            ]);
        } catch (\PDOException $e) {
            throw ApplicationException::of($e->getMessage(), $e);
        }

        return $this;

    }

    /**
     * @return $this
     */
    public function disconnect() {

        $this->pdo = null;

        return $this;

    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public static function doInConnection(callable $callable) {

        if (self::hasInstance()) {

            $result = self::getInstance()->doInTransaction($callable);

        } else {

            $conn = self::getInstance();
            $conn->connect();
            $result = $conn->doInTransaction($callable);
            $conn->disconnect();

            self::killInstance();

        }

        return $result;

    }

    /**
     * @param callable(Database) $callable
     * @return mixed
     */
    public function doInTransaction(callable $callable) {

        $result = call_user_func($callable, $this);

        return $result;

    }

    /**
     * @return DBQuery
     */
    public function getDBQuery() {
        return DBQuery::getInstance();
    }

    /**
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollBack();
    }

    public function finishTransaction() {
        return $this->pdo->rollBack();
    }

    /**
     * @param string $query
     * @param array $params
     * @return string
     */
    public function queryQuote($query, $params = []) {

        $position = 0;

        $arguments = preg_replace_callback("/(\\?)|(\\:\\w+)/", function ($match) use ($params, &$position) {
            $array_key = $match[0] === '?' ? $position++ : $match[0];
            if (!isset($params[$array_key])) {
                return 'NULL';
            }
            return is_numeric($params[$array_key]) ? $params[$array_key] : $this->pdo->quote($params[$array_key],
                PDO::PARAM_STR);
        }, $query);


        return $arguments;

    }

    public function executePool(DBQueryPool $pool) {
        /** @var DBQueryWrapper $wrapper */
        foreach($pool as $wrapper) {
            $this->justExecute($wrapper->getQueryBody(), $wrapper->getQueryParams());
        }
    }

    public static function executePoolInConnection(DBQueryPool $pool) {
        $connection = new self();
        $connection->connect();
        $connection->beginTransaction();
        $connection->executePool($pool);
        $connection->commit();
        $connection->disconnect();
    }

    /**
     * @param $query
     * @param $params
     * @return \PDOStatement
     * @throws ControllerException
     */
    private function createResource($query, $params) {

        if ($query instanceof QueryBuilder) {
            $queryString = $this->queryQuote(
                $query->getQuery($this->pdo),
                $query->getParameters());
        } else {
            $queryString = $this->queryQuote($query, $params);
        }


        $resource = $this->pdo->prepare($queryString);

        if ($resource === false) {
            throw new ControllerException($this->pdo->errorInfo()[2]);
        }

        $resource->execute();

        if ($resource->errorCode() !== "00000") {
            throw new ControllerException($resource->errorInfo()[2]);
        }

        return $resource;

    }

    /**
     * @param string $query
     * @param array $params
     * @param string $key
     * @param Callable $callback
     * @return array
     * @throws ControllerException
     */
    public function fetchAll($query, array $params = null, $key = null, $callback = null) {

        $resource = $this->createResource($query, $params);

        $result = [];

        while ($row = $resource->fetch(PDO::FETCH_ASSOC)) {

            if (is_callable($callback)) {
                $row = call_user_func($callback, $row);
            }

            if (!is_null($key)) {
                $k = $row[$key];
                unset($row[$key]);
                $result[$k] = $row;
            } else {
                $result[] = $row;
            }

        }

        return $result;

    }


    /**
     * @param string $query
     * @param array $params
     * @param Callable $callback
     * @return Optional
     * @throws ControllerException
     */
    public function fetchOneRow($query, array $params = null, $callback = null) {

        $resource = $this->createResource($query, $params);

        $row = $resource->fetch(PDO::FETCH_ASSOC);

        if ($row !== false && is_callable($callback)) {
            $row = call_user_func($callback, $row);
        }

        return Optional::ofDeceptive($row);

    }

    /**
     * @param string $query
     * @param array $params
     * @param int $column
     * @return Optional
     * @throws ControllerException
     */
    public function fetchOneColumn($query, array $params = null, $column = 0) {

        $resource = $this->createResource($query, $params);

        $row = $resource->fetchColumn($column);

        return Optional::ofDeceptive($row);

    }

    /**
     * @param string|QueryBuilder $query
     * @param array $params
     * @param string $class
     * @param array|null $args
     * @return Optional
     * @throws ControllerException
     */
    public function fetchOneObject($query, array $params = null, $class, array $args = []) {

        $resource = $this->createResource($query, $params);

        $object = $resource->fetchObject($class, $args);

        return Optional::ofDeceptive($object);

    }

    /**
     * @param string $query
     * @param array|null $params
     * @param $class
     * @param array|null $args
     * @return array
     * @throws ControllerException
     */
    public function fetchAllObjects($query, array $params = null, $class, array $args = null) {

        $resource = $this->createResource($query, $params);

        $objects = $resource->fetchAll(PDO::FETCH_CLASS, $class, $args);

        return $objects;

    }

    /**
     * @param string $query
     * @param array|null $params
     * @return int
     * @throws ControllerException
     */
    public function executeUpdate($query, array $params = null) {

        $resource = $this->createResource($query, $params);

        return $resource->rowCount();

    }

    /**
     * @param string $query
     * @param array|null $params
     * @return mixed
     * @throws ControllerException
     */
    public function executeInsert($query, array $params = null) {

        $this->createResource($query, $params);

        return $this->pdo->lastInsertId(null);

    }

    /**
     * @param string|QueryBuilder $query
     * @param array $params
     */
    public function justExecute($query, array $params = null) {

        $this->createResource($query, $params)->closeCursor();

    }


}
