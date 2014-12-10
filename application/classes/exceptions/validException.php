<?php

/**
 * Description of validException
 *
 * @author Roman
 */
class validException extends morException
{
    public function __construct($message = null, $context = null)
    {
        parent::__construct($message, 2001, null, $context);
    }
}
