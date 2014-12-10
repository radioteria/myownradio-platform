<?php

class validStreamDescription extends validVariable
{
    function __construct($data)
    {
        if(strlen($data) > 4096)
        {
            throw new validException("Stream description may contain up to 4096 chars!");
        }
        $this->variable = $data;
    }
}
