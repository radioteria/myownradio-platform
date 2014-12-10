<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validStreamState
 *
 * @author Roman
 */
class validStreamState extends validVariable
{
    public function __construct($state)
    {
        if($state == 1 || $state == 0)
        {
            $this->variable = (int) $state;
        }
        else
        {
            throw new validException("Invalid stream state", "state");
        }
    }
}
