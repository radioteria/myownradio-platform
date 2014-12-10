<?php

class validPassword extends validVariable
{
    
    function __construct($data, $context = "password")
    {
        $this->context = $context;
        
        if(empty($data))
        {
            throw new validException("Password must be set!", $this->context);
        }
        
        if(strlen($data) < 5)
        {
            throw new validException("Password is too short!", $this->context);
        }
        
        if(strlen($data) > 255)
        {
            throw new validException("Password is too long!", $this->context);
        }
        
        if(preg_match("/^[a-z]+$|^[0-9]+$/", $data))
        {
            throw new validException("Password is too simple!", $this->context);
        }
        
        $this->variable = $data;
    }
    
    function md5()
    {
        return md5($this->variable);
    }
}
