<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tags
 *
 * @author Roman
 */
class Tags
{
    public static function getList($filter = null)
    {
        if ($filter === null)
            $results = Database::getInstance()->query("SELECT * FROM `r_genres` LIMIT 30");
        else
            $results = Database::getInstance()->query("SELECT * FROM `r_genres` WHERE `genre` LIKE ? LIMIT 30", array($filter . "%"));
        return $results;
    }
}
