<?php

class validUniqueId extends validVariable
{
    public function __construct($variable)
    {
        $db = Database::getInstance();
        
        if ((int)$db->query_single_col("SELECT COUNT(*) FROM `r_link` WHERE `unique_id` = ?", array($variable)) === 0)
        {
            throw new validException("Thack with this unique id (${variable}) not exists", null);
        }
        
        $this->variable = $variable;
    }
}
