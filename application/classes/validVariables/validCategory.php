<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validCategory
 *
 * @author Roman
 */
class validCategory extends validVariable
{
    function __construct($data)
    {
        $db = Database::getInstance();
        
        $result = (int) $db->query_single_col("SELECT COUNT(*) FROM `r_categories` WHERE `id` = ?", array($data));
        if($result === 0)
        {
            throw new validException("Incorrect category!");
        }
        
        $this->variable = $data;
    }
}
