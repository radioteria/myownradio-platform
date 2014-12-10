<?php

class patNothingChangedException extends morException
{
    public function __construct($message = null, $code = null, $previous = null, $context = null)
    {
        parent::__construct("Nothing has been done", $code, $previous, $context);
    }
}
