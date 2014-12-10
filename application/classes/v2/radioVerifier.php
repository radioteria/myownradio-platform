<?php

class radioVerifier extends Model
{
    public function checkUserEmailExists($email)
    {
        return (bool) $this->database->query_single_col("SELECT COUNT(*) FROM `r_users` WHERE `mail` = ?", array($email));
    }
    
    public function checkUserLogin(validLogin $login, validPassword $password) 
    {
        $database = Database::getInstance();
        $data = $database->query_single_col("SELECT `uid` FROM `r_users` WHERE `login` = ? AND `password` =? LIMIT 1",
            array($login->get(), md5($login->get() . $password->get()))
        );
        
        return $data;
    }
}
