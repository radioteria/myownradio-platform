<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of validTrackId
 *
 * @author Roman
 */
class validTrackId extends validVariable
{
    public function __construct($trackId)
    {
        $db = Database::getInstance();
        
        if ((int)$db->query_single_col("SELECT COUNT(*) FROM `r_tracks` WHERE `tid` = ?", array($trackId)) === 0)
        {
            throw new validException("Thack with this id (${variable}) not exists", null);
        }
        
        $this->variable = $variable;
    }
}
