<?php

class controller
{
    /** @var string */
    protected $route;
    /** @var Database */
    protected $database;

    public function __construct($route)
    {
        $this->route = $route;
        $this->database = Database::getInstance();
    }
    
}
