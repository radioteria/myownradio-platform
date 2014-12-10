<?php

class controller
{
    protected $route;
    protected $database;

    public function __construct($route)
    {
        $this->route = $route;
        $this->database = Database::getInstance();
    }
    
}
