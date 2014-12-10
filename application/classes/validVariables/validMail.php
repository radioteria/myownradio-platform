<?php

class validMail extends validVariable
{
    function __construct($data)
    {
        if (!preg_match("/^[\w\S]+@[\w\S]+\.[\w]{2,4}$/m", $data))
        {
            throw new validException("You entered incorrect email address", "email");
        }
        
        $this->variable = $data;
    }
}
