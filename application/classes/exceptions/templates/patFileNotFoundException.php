<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of patFileNotFoundException
 *
 * @author Roman
 */
class patFileNotFoundException extends Exception
{
    public function __construct($file)
    {
        parent::__construct("File '" . $file . "' not found", 800, null);
    }
}
