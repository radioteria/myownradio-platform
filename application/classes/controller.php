<?php

class controller
{
    protected $route;
    protected $database;

    public function __construct($route)
    {
        /** @var string route */
        $this->route = $route;
        /** @var Database database */
        $this->database = Database::getInstance();
    }
    
}
