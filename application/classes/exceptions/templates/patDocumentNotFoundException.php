<?php

class patDocumentNotFoundException extends morException
{
    public function __construct(Exception $ex = null)
    {
        parent::__construct("Requested document not found on this server", 404, $ex);
    }
}
