<?php

namespace Tools;

use BaseQuery;
use Exception;
use FluentPDO;
use MVC\Services\ApplicationConfig;
use PDO;
use PDOStatement;
use Tools\Singleton;

class Database {

    use Singleton;

    private $pdo;
    private $count = 0;
    private $debug = 0;

    public function __construct() {
        $settings = ApplicationConfig::getInstance()->getSection('database')->getOrElse([
            "db_database" => "myownradio",
            "db_login" => "root",
            "db_password" => ""
        ]);
        $this->pdo = new PDO("mysql:unix_socket=/tmp/mysql.sock;dbname={$settings['db_database']}",
            $settings['db_login'], $settings['db_password'], array(PDO::ATTR_PERSISTENT => true));
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    }

    /**
     * @return FluentPDO
     */
    public function getFluentPDO() {
        return new FluentPDO($this->getPDO());
    }

    /**
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    /**
     * @deprecated
     * @param string $value
     * @return string
     */
    public function quote($value) {
        return $this->pdo->quote($value);
    }

    /**
     * @param string $query
     * @param array $params
     * @return string
     */
    public function query_quote($query, $params = array()) {

        $position = 0;

        $arguments = preg_replace_callback("/(\\?)|(\\:\\w+)/", function ($match) use ($params, &$position) {
            $array_key = $match[0] === '?' ? $position++ : $match[0];
            if (!isset($params[$array_key])) {
                throw new Exception(sprintf("No value for variable %s present in parameters array!", $match[0]));
            }
            return is_numeric($params[$array_key]) ? $params[$array_key] : $this->pdo->quote($params[$array_key], PDO::PARAM_STR);
        }, $query);

        return $arguments;

    }

    /**
     * @param string $query
     * @return PDOStatement
     */
    public function createStatement($query) {
        return $this->pdo->prepare($query);
    }

    /**
     * @param callable $callback
     * @return string
     */
    public function createQuery(callable $callback) {
        $fluent = $this->getFluentPDO();
        /** @var BaseQuery $query */
        $query = call_user_func($callback, $fluent);
        return $this->query_quote($query->getQuery(false), $query->getParameters());
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $key
     * @param Callable $callback
     * @return array
     */
    public function fetchAll($query, $params = array(), $key = null, $callback = null) {
        $res = $this->pdo->prepare($this->query_quote($query, $params));
        $res->execute();
        $result = [];

        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {

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
     */
    public function fetchOneRow($query, $params = array(), $callback = null) {

        $res = $this->pdo->prepare($this->query_quote($query, $params));
        $res->execute();

        $row = $res->fetch(PDO::FETCH_ASSOC);

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
     */
    public function fetchOneColumn($query, $params = [], $column = 0) {

        $res = $this->pdo->prepare($this->query_quote($query, $params));
        $res->execute();

        $row = $res->fetchColumn($column);

        return Optional::ofDeceptive($row);

    }

    /**
     * @param string $query
     * @param array $params
     * @return int
     */
    public function executeUpdate($query, $params = []) {
        $res = $this->pdo->prepare($this->query_quote($query, $params));
        $res->execute();
        return $res->rowCount();
    }

    /**
     * @param string $query
     * @param array $params
     * @param string $key
     * @return int
     */
    public function executeInsert($query, $params = [], $key = null) {
        $res = $this->pdo->prepare($this->query_quote($query, $params));
        $res->execute();
        return $this->pdo->lastInsertId($key);
    }

    /**
     * @deprecated
     */
    public function query_universal(/* String */ $query, /* String */ $key = null, callable $callback = null) {

        $res = $this->pdo->prepare($query);
        $res->execute();
        $result = [];

        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {

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
     * @deprecated
     * @param $query
     * @param array $params
     * @return array
     */
    public function query($query, $params = []) {
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);

        $this->count++;
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @deprecated
     * @param $query
     * @param array $params
     * @return null
     */
    public function query_single_col($query, $params = array()) {
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);
        if ($res->rowCount() > 0) {
            $val = $res->fetch(PDO::FETCH_NUM);
            $this->count++;
            return $val[0];
        } else {
            return null;
        }
    }

    /**
     * @deprecated
     * @param $query
     * @param array $params
     * @return mixed|null
     */
    public function query_single_row($query, $params = array()) {
        // todo: fix this!!!!
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);

        if ($res->rowCount() > 0) {
            $this->count++;
            return $res->fetch(PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    /**
     * @deprecated
     * @param $query
     * @param array $params
     * @return int
     */
    public function query_update($query, $params = array()) {
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);

        $this->count++;
        return $res->rowCount();
    }

    public function lastError() {
        $arr = $this->pdo->errorInfo();
        return $arr[0];
    }

    public function lastInsertId($name = null) {
        return $this->pdo->lastInsertId($name);
    }

    public function __toString() {
        return (string) $this->count;
    }

}
