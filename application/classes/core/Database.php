<?php

class Database
{
    use Singleton;
    
    private $pdo;
    private $count = 0;
    private $debug = 0;

    public function __construct()
    {
        $settings = config::getSection('database');
        try 
        {
            $this->pdo = new PDO("mysql:unix_socket=/tmp/mysql.sock;dbname={$settings['db_database']}",
                    $settings['db_login'], $settings['db_password'], array(PDO::ATTR_PERSISTENT => true));
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        } catch (Exception $ex) {
            throw new databaseException($ex->getMessage(), 2502, null);
        }
    }
    
    public function __destruct() 
    {
        unset($this->pdo);
    }

    public static function getFluentPDO() {
        return new FluentPDO(self::getInstance());
    }

    public function quote($value)
    {
        return $this->pdo->quote($value);
    }

    public function query_quote(/* string */ $query, /* array */ $params) {

        $position = 0;

        $arguments = preg_replace_callback("/(\\?)|(\:\w+)/", function ($match) use ($params, &$position) {
            $array_key = $match[0] === '?' ? $position++ : $match[0];
            if (!isset($params[$array_key])) {
                throw new Exception(sprintf("No value for variable %s present in parameters array!", $match[0]));
            }
            return is_numeric($params[$array_key]) ? $params[$array_key] : $this->pdo->quote($params[$array_key], PDO::PARAM_STR);
        }, $query);

        return $arguments;

    }

    public function query_universal(/* string */ $query, /* string */ $key = null, callable $callback = null) {

        $res = $this->pdo->prepare($query);
        $res->execute();
        $result = [];

        while ($row = $res->fetch(PDO::FETCH_ASSOC))
        {

            if (is_callable($callback))
            {
                $row = call_user_func($callback, $row);
            }

            if(!is_null($key))
            {
                $k = $row[$key];
                unset($row[$key]);
                $result[$k] = $row;
            }
            else
            {
                $result[] = $row;
            }

        }
        return $result;
    }

    public function query($query, $params = [])
    {
        $res = $this->pdo->prepare($query);

        if($this->debug) { $timeStart = microtime(true); }
        $res->execute($params);
        if($this->debug) { misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart)); }
        
        /* Error thrower */
        if($res->errorCode() != "0000")
        {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

        $this->count ++;
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function query_single_col($query, $params = array())
    {
        $res = $this->pdo->prepare($query);

        if($this->debug) { $timeStart = microtime(true); }
        $res->execute($params);
        if($this->debug) { misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart)); }
        
        /* Error thrower */
        if($res->errorCode() != "0000")
        {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

        if ($res->rowCount() > 0)
        {
            $val = $res->fetch(PDO::FETCH_NUM);
            $this->count ++;
            return $val[0];
        }
        else
        {
            return null;
        }
    }

    public function query_single_row($query, $params = array())
    {
        // todo: fix this!!!!
        $res = $this->pdo->prepare($query);
        
        if($this->debug) { $timeStart = microtime(true); }
        $res->execute($params);
        if($this->debug) { misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart)); }

        /* Error thrower */
        if($res->errorCode() != "0000")
        {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }

        if ($res->rowCount() > 0)
        {
            $this->count ++;
            return $res->fetch(PDO::FETCH_ASSOC);
        }
        else
        {
            return null;
        }
    }

    public function query_update($query, $params = array())
    {
        if ($this->pdo == null)
        {
            self::connect();
        }
        
        $res = $this->pdo->prepare($query);

        if($this->debug) { $timeStart = microtime(true); }
        $res->execute($params);
        if($this->debug) { misc::writeDebug(sprintf("Query: {$query} @ %0.8f", microtime(true) - $timeStart)); }
        
        /* Error thrower */
        if($res->errorCode() != "0000")
        {
            $err = $res->errorInfo();
            throw new databaseException($err[2], 2502);
        }
        
        $this->count ++;
        return $res->rowCount();
    }

    public function lastError()
    {
        $arr = $this->pdo->errorInfo();
        return $arr[0];
    }
    
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }
    
    public function __toString()
    {
        return $this->count;
    }

}
