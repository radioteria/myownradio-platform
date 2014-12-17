<?php

namespace MVC\Services;

use BaseQuery;
use FluentPDO;
use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Services\DB\DBQuery;
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
            "db_database" => "myownradio",
            "db_login" => "root",
            "db_password" => "",
            "db_hostname" => "127.0.0.1"
        ]);

        $this->connect();

    }

    /**
     * @return $this
     */
    public function connect() {

        $this->pdo = new PDO("mysql:unix_socket=/tmp/mysql.sock;dbname={$this->settings['db_database']}",
            $this->settings['db_login'], $this->settings['db_password'], array(PDO::ATTR_PERSISTENT => true));

        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        return $this;

    }

    /**
     * @return $this
     */
    public function disconnect() {

        $this->pdo = null;

        return $this;

    }

    public static function doInTransaction(callable $callable) {

        $connection = new self();
        $connection->beginTransaction();

        $result = call_user_func_array($callable, [$connection, DBQuery::getInstance()]);

        $connection->disconnect();

        return $result;

    }

    /**
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
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

    /**
     * @param callable $callback
     * @return string
     * @deprecated
     */
    public function createQuery(callable $callback) {

        $fluent = $this->getFluentPDO();
        /** @var BaseQuery $query */
        $query = call_user_func($callback, $fluent);

        return $this->queryQuote($query->getQuery(false), $query->getParameters());

    }

    /**
     * @param $query
     * @param $params
     * @return \PDOStatement
     * @throws ControllerException
     */
    private function createResource($query, $params) {

        if ($query instanceof QueryBuilder) {
            $queryString = $query->getQuery($this->pdo);
            $queryParams = $query->getParameters();
        } else {
            $queryString = $this->queryQuote($query, $params);
            $queryParams = null;
        }

        $resource = $this->pdo->prepare($queryString);
        $resource->execute($queryParams);

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
    public function fetchAll($query, $params = [], $key = null, $callback = null) {

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
    public function fetchOneRow($query, $params = array(), $callback = null) {

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
    public function fetchOneColumn($query, $params = [], $column = 0) {

        $resource = $this->createResource($query, $params);

        $row = $resource->fetchColumn($column);

        return Optional::ofDeceptive($row);

    }

    /**
     * @param string $query
     * @param array $params
     * @param string $class
     * @return Optional
     * @throws ControllerException
     */
    public function fetchOneObject($query, $params = [], $class) {

        $resource = $this->createResource($query, $params);

        $object = $resource->fetchObject($class);

        return Optional::ofDeceptive($object);

    }

    /**
     * @param string $query
     * @param array $params
     * @param $class
     * @return array
     * @throws ControllerException
     */
    public function fetchAllObjects($query, $params = [], $class) {

        $resource = $this->createResource($query, $params);

        $objects = $resource->fetchAll(PDO::FETCH_CLASS, $class);

        return $objects;

    }

    /**
     * @param string $query
     * @param array $params
     * @return int
     * @throws ControllerException
     */
    public function executeUpdate($query, $params = []) {

        $resource = $this->createResource($query, $params);

        return $resource->rowCount();

    }

    /**
     * @param string $query
     * @param array $params
     * @return mixed
     * @throws ControllerException
     */
    public function executeInsert($query, $params = []) {

        $this->createResource($query, $params);

        return $this->pdo->lastInsertId(null);

    }

}
