<?php

class validStreamName extends validVariable
{
    function __construct($data)
    {
        if(empty($data) || strlen($data) < 3 || strlen($data) > 32)
        {
            throw new validException(sprintf("Stream name must contain from 3 to 32 chars"));
        }
        
        $this->variable = $data;
    }
}
