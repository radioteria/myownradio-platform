<?php

class authController extends controller {

    public function __construct($route) {

        parent::__construct($route);

        $visitor = Visitor::getInstance();

        if ($visitor->getId() === 0) {
            throw new patNoPermissionException();
        }

    }

}
