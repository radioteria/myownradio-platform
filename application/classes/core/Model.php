<?php

abstract class Model {

    /**
     * @var Database
     */
    protected $database;
    
    public function __construct() {
        $this->database = Database::getInstance();
    }
    
    
}
