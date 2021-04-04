<?php

namespace Framework\Services;

use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\DatabaseException;
use Framework\Injector\Injectable;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\DBQueryPool;
use Framework\Services\DB\DBQueryWrapper;
use Framework\Services\DB\Query\QueryBuilder;
use PDO;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class Database implements SingletonInterface, Injectable
{

    use Singleton;

    /** @var PDO $pdo */
    private $pdo;
    private $config;

    private static $cache = [];

    public function __construct()
    {
        $this->config = config("database.connections.mysql");
    }

    /**
     * @return $this
     * @throws ApplicationException
     */
    public function connect()
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s",
                $this->config["driver"],
                $this->config["host"],
                $this->config["port"],
                $this->config["database"]
            );
            $this->pdo = new PDO(
                $dsn,
                $this->config["username"],
                $this->config["password"],
                $this->config["attributes"]
            );
        } catch (\PDOException $e) {
            throw ApplicationException::of($e->getMessage(), null, $e);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function disconnect()
    {

        $this->pdo = null;

        return $this;
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public static function doInConnection(callable $callable)
    {

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
     * @param callable $callable
     * @return mixed
     */
    public function doInTransaction(callable $callable)
    {

        $result = call_user_func($callable, $this);

        return $result;
    }

    /**
     * @return DBQuery
     */
    public function getDBQuery()
    {
        return DBQuery::getInstance();
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    public function finishTransaction()
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param string $query
     * @param array $params
     * @return string
     */
    public function queryQuote($query, $params = [])
    {

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

    public function executePool(DBQueryPool $pool)
    {
        /** @var DBQueryWrapper $wrapper */
        foreach ($pool as $wrapper) {
            $this->justExecute($wrapper->getQueryBody(), $wrapper->getQueryParams());
        }
    }

    public static function executePoolInConnection(DBQueryPool $pool)
    {
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
     * @return string
     */
    private function createQueryString($query, $params = null)
    {
        if ($query instanceof QueryBuilder) {
            return $this->queryQuote(
                $query->getQuery($this->pdo),
                $query->getParameters());
        } else {
            return $this->queryQuote($query, $params);
        }
    }

    /**
     * @param $query
     * @param $params
     * @param bool $cached
     * @throws \Framework\Exceptions\DatabaseException
     * @return \PDOStatement
     */
    private function createResource($query, $params = null, $cached = false)
    {

        $queryString = $this->createQueryString($query, $params);

        $resource = $this->pdo->prepare($queryString);

        if ($resource === false) {
            throw new DatabaseException($this->pdo->errorInfo()[2], $queryString);
        }

        $resource->execute();

        if ($resource->errorCode() !== "00000") {
            throw new DatabaseException($resource->errorInfo()[2], $queryString);
        }

        return $resource;
    }

    private function createResourceFromString($queryString)
    {

        $resource = $this->pdo->prepare($queryString);

        if ($resource === false) {
            throw new DatabaseException($this->pdo->errorInfo()[2], $queryString);
        }

        $resource->execute();

        if ($resource->errorCode() !== "00000") {
            throw new DatabaseException($resource->errorInfo()[2], $queryString);
        }

        return $resource;
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $key
     * @param Callable $callback
     * @param bool $cached
     * @return array
     */
    public function fetchAll($query, array $params = null, $key = null, callable $callback = null, $cached = false)
    {

        $queryString = $this->createQueryString($query, $params);

        if ($cached == true && isset(self::$cache[$queryString])) {
            $db_result = self::$cache[$queryString];
        } else {
            $resource = $this->createResourceFromString($queryString);
            $db_result = $resource->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($cached == true) {
            self::$cache[$queryString] = $db_result;
        }

        $result = [];

        foreach ($db_result as $i => $row) {
            if (is_callable($callback)) {
                $row = call_user_func_array($callback, [$row, $i]);
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
     * @param $query
     * @param array $params
     * @param callable $callback
     */
    public function eachRow($query, array $params = null, callable $callback)
    {

        $resource = $this->createResource($query, $params);

        while ($row = $resource->fetch(PDO::FETCH_ASSOC)) {
            call_user_func($callback, $row);
            unset($row);
        }

        $resource->closeCursor();
    }

    /**
     * @param string $query
     * @param array $params
     * @param Callable $callback
     * @return Optional
     * @throws ControllerException
     */
    public function fetchOneRow($query, array $params = null, $callback = null)
    {

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
    public function fetchOneColumn($query, array $params = null, $column = 0)
    {

        $resource = $this->createResource($query, $params);

        $row = $resource->fetchColumn($column);

        if (is_numeric($row)) {
            $row = intval($row);
        }

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
    public function fetchOneObject($query, array $params = null, $class, array $args = [])
    {

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
    public function fetchAllObjects($query, array $params = null, $class, array $args = null)
    {

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
    public function executeUpdate($query, array $params = null)
    {

        $resource = $this->createResource($query, $params);

        //error_log("SQL: " . $resource->queryString);

        return $resource->rowCount();
    }

    /**
     * @param string $query
     * @param array|null $params
     * @return mixed
     * @throws ControllerException
     */
    public function executeInsert($query, array $params = null)
    {

        $this->createResource($query, $params);

        return $this->pdo->lastInsertId(null);
    }

    /**
     * @param string|QueryBuilder $query
     * @param array $params
     */
    public function justExecute($query, array $params = null)
    {

        $this->createResource($query, $params)->closeCursor();
    }

    public function quote($var)
    {

        return $this->pdo->quote($var, PDO::PARAM_STR);
    }
}
