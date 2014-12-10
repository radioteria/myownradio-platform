<?php

class patFileNotDeletedException extends Exception
{
    public function __construct($filename)
    {
        parent::__construct("Could not delete file '" . $filename . "'", 801, null);
    }
}
