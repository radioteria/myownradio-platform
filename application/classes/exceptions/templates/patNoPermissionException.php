<?php

class patNoPermissionException extends morException
{
    public function __construct(Exception $ex = null)
    {
        parent::__construct("No permission", 403, $ex);
    }
}
