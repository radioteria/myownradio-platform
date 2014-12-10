<?php

abstract class validVariable
{
    protected $variable = null;
    protected $context = null;
            
    function get()
    {
        return $this->variable;
    }
    
    function __toString()
    {
        return (string) $this->variable;
    }
    
    function __destruct()
    {
        unset($this->variable);
    }
    
    function length()
    {
        return strlen($this->variable);
    }
}

