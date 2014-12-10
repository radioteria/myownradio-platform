<?php

class post_controller extends router
{
    
    public function login()
    {
        try
        {
            $login = new validLogin(application::post("login", null, REQ_STRING));
            $passw = new validPassword(application::post("password", null, REQ_STRING));
            $vendee = new Visitor(1);
            echo $vendee->get_email();
        }
        catch(Exception $ex)
        {
            echo misc::outputJSON("BAD_CREDENTIALS");
        }
    }
}