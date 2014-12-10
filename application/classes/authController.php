<?php

class authController extends controller
{
    public function __construct($route)
    {
        parent::__construct($route);
        
        $vendee = Visitor::getInstance();
        
        if($vendee->getId() === 0)
        {
            throw new patNoPermissionException();
        }
    }
}
