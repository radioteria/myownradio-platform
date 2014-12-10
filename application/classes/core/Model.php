<?php

abstract class Model {

    protected $database;
    
    public function __construct() {
        $this->database = Database::getInstance();
    }
    
    
}
