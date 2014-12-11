<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 11.12.14
 * Time: 17:36
 */

class PDOExtended extends PDO {
    function __construct($dsn, $username, $passwd, $options) {
        parent::__construct($dsn, $username, $passwd, $options);
    }
}