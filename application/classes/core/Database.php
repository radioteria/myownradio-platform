<?php

class Database {

    use Singleton;

    private $pdo;
    private $count = 0;
    private $debug = 0;

    public function __construct() {
        $settings = config::getSection('database');
        try {
            $this->pdo = new PDOExtended("mysql:unix_socket=/tmp/mysql.sock;dbname={$settings['db_database']}",
                $settings['db_login'], $settings['db_password'], array(PDO::ATTR_PERSISTENT => true));
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        } catch (Exception $ex) {
            throw new databaseException($ex->getMessage(), 2502, null);
        }
    }

    public function __destruct() {
        unset($this->pdo);
    }

    public static function getFluentPDO() {
        return new FluentPDO(self::getInstance()->getPDO());
    }

    /**
     * @return PDOExtended
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

        return Optional::ofFalse($row);

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
     * @throws databaseException
     */
    public function query($query, $params = []) {
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);
        if ($this->debug) {
            misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart));
        }

        /* Error thrower */
        if ($res->errorCode() != "0000") {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

        $this->count++;
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @deprecated
     * @param $query
     * @param array $params
     * @return null
     * @throws databaseException
     */
    public function query_single_col($query, $params = array()) {
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);
        if ($this->debug) {
            misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart));
        }

        /* Error thrower */
        if ($res->errorCode() != "0000") {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

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
     * @throws databaseException
     */
    public function query_single_row($query, $params = array()) {
        // todo: fix this!!!!
        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);
        if ($this->debug) {
            misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart));
        }

        /* Error thrower */
        if ($res->errorCode() != "0000") {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

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
     * @throws databaseException
     */
    public function query_update($query, $params = array()) {
        if ($this->pdo == null) {
            self::connect();
        }

        $res = $this->pdo->prepare($query);

        if ($this->debug) {
            $timeStart = microtime(true);
        }
        $res->execute($params);
        if ($this->debug) {
            misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart));
        }

        /* Error thrower */
        if ($res->errorCode() != "0000") {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

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
