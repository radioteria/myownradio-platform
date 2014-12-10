<?php

class databaseException extends morException
{
    public function __construct($message = null, $code = null, $previous = null, $context = null)
    {
        parent::__construct($message, $code, $previous, $context);
    }
}
