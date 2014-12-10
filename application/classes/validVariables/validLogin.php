<?php

class validLogin extends validVariable
{
    function __construct($data)
    {
        if (empty($data) || strlen($data) < 3 || strlen($data) > 255)
        {
            throw new validException("Login must contain from 3 to 255 chars!", "login");
        }

        if (preg_match("/\s+/", $data))
        {
            throw new validException("Login must not contain spaces!", "login");
        }

        $this->variable = $data;
    }
}
