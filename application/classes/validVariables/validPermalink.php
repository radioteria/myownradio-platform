<?php

class validPermalink extends validVariable
{
    function __construct($data)
    {
        if (!preg_match("/(^[a-z0-9\-]*$)/m", $data))
        {
            throw new validException("Permalink must contain only lower case latin symbols, numbers or dashes");
        }
        
        $this->variable = $data;
    }
}
