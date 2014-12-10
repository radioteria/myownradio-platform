<?php

class patFileNotWritableException extends Exception
{
    public function __construct()
    {
        parent::__construct("File could not be written", 802, null);
    }
}
