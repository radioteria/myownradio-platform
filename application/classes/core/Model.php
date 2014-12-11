<?php

abstract class Model {

    protected $database;
    
    public function __construct() {
        /** @var Database $db */
        $this->database = Database::getInstance();
    }
    
    
}
