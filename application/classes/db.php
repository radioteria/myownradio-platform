<?php

class db
{

    private static 
            $pdo = null,
            $count = 0;

    public static function connect()
    {
        $settings = config::getSection('database');
        try
        {
            self::$pdo = new PDO("mysql:unix_socket=/tmp/mysql.sock;dbname={$settings['db_database']}", $settings['db_login'], $settings['db_password']);
        }
        catch(Exception $ex)
        {
            die("<h1>Could not connect to the database</h1>");
        }
    }
    
    public static function disconnect() {
        self::$pdo = null;
    }

    public static function quote($value)
    {
        if (self::$pdo == null)
        {
            self::connect();
        }
        return self::$pdo->quote($value);
    }
    
    public static function query($query, $params = array())
    {
        if (self::$pdo == null)
        {
            self::connect();
        }

        $res = self::$pdo->prepare($query);
        if ($res)
        {
            $res->execute($params);
            self::$count ++;
            //misc::writeDebug("QUERY: {$query}");
            return $res->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return null;
        }
    }

    public static function query_single_col($query, $params = array())
    {
        if (self::$pdo == null)
        {
            self::connect();
        }

        $res = self::$pdo->prepare($query);
        if ($res)
        {
            $res->execute($params);
            if( $res->rowCount() > 0 )
            {
                $val = $res->fetch(PDO::FETCH_NUM);
                self::$count ++;
                //misc::writeDebug("QUERY: {$query}");
                return $val[0];
            }
        }
        return null;
    }

    public static function query_single_row($query, $params = array())
    {
        if (self::$pdo == null)
        {
            self::connect();
        }

        $res = self::$pdo->prepare($query);
        if ( is_null($res) )
        {
            return null;
        }

        $res->execute($params);
        if ( $res->rowCount() > 0 )
        {
            self::$count ++;
            //misc::writeDebug("QUERY: {$query}");
            return $res->fetch(PDO::FETCH_ASSOC);
        }
        else
        {
            return null;
        }
    }

    public static function query_update($query, $params = array())
    {
        if (self::$pdo == null)
        {
            self::connect();
        }
        
        $res = self::$pdo->prepare($query);
        $res->execute($params);
        self::$count ++;
        //misc::writeDebug("QUERY: {$query}");
        return $res->rowCount();
    }

    public static function lastError()
    {
        return self::$pdo->errorInfo();
    }
    
    public static function lastInsertId($name = NULL)
    {
        return self::$pdo->lastInsertId($name);
    }
    
    public static function totalQueries()
    {
        return self::$count;
    }

}
