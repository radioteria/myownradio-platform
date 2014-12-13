<?php

class authController extends controller {

    public function __construct($route) {

        parent::__construct($route);

        $visitor = User::getInstance();

        if ($visitor->getId() === 0) {
            throw new patNoPermissionException();
        }

    }

}
