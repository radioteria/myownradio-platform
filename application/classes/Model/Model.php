<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 23:09
 */

namespace Model;

use Tools\Database;

class Model {

    /** @var Database */
    protected $db;

    function __construct() {
        $this->db = Database::getInstance();
    }

}