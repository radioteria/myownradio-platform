<?php

class loop
{

    private $container = array();

    public function __construct()
    {
        $args = func_get_args();
        if (count($args) > 0)
        {
            $this->container[] = $args[0];
        }
    }
    
    public function __destruct()
    {
        // Clear variables
        unset($this->container);
    }
    
    public function purge()
    {
        $this->container = array();
        return $this;
    }

    
    public function add($el)
    {
        $this->container[] = $el;
        return $this;
    }

    public function each($callback)
    {
        foreach($this->container as $track)
        {
            $ret = call_user_func($callback, $track);
            if($ret === false)
            {
                break;
            }
        }
    }

    public function length()
    {
        return count($this->container);
    }

    public function __toString()
    {
        return print_r($this->container, true);
    }

    public function toArray()
    {
        return $this->container;
    }
 
}
