<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validSomething
 *
 * @author Roman
 */
class validSomething extends validVariable
{
    public function __construct($data, $context = null)
    {
        if (strlen($data) === 0 || $data === null)
        {
            throw new validException("This field must be filled", $context);
        }
        $this->variable = $data;
    }
}
