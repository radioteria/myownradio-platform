<?php

class post_controller extends controller 
{
    public function login() 
    {
        $login      = new validLogin(application::post("login", null, REQ_STRING));
        $password   = new validPassword(application::post("password", null, REQ_STRING));
        $saveCookie = application::post('remember', 'off', REQ_STRING);
        
        $uid = (new radioVerifier())->checkUserLogin($login, $password);
                
        if ($uid === null) {
            throw new morException("Incorrect login or password", 3002, null, "login");
        }
        
        if($saveCookie === "on")
        {
            session::init(true);
        }
            
        $database = Database::getInstance();
                
        $database->query_update('UPDATE `r_users` SET `last_visit_date` = ? WHERE `uid` = ?', array(time(), $uid));

        // Generate token
        $token = Visitor::createToken($uid, application::getClient(), 
            filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'), session::getID());
            
        session::set('authtoken', $token);
        
        echo misc::okJSON();
    }
    
    public function logout() 
    {
        $database = Database::getInstance();
        
        $token = session::get('authtoken');
        
        $database->query_update("DELETE FROM `r_sessions` WHERE `token` = ?", array($token));
        
        session::remove('authtoken');
        session::end();
        
        return misc::okJSON();
    }

    public function requestRegistration()
    {
        $submit = application::post("submit", false, REQ_BOOL);
        $email = new validMail(application::post("email", null, REQ_STRING));

        // Check email presence in database
        if ((new radioVerifier())->checkUserEmailExists($email))
        {
            throw new morException("User with this email already registered", 3001, null, "email");
        }

        // Send email 
        if($submit === true) 
        {
            $code = md5($email . "@myownradio.biz@" . $email->get());
            $confirm = base64_encode(json_encode(array('email' => $email->get(), 'code' => $code)));
            
            $subject    = "Registration on myownradio.biz";
            $sender     = "The MyOwnRadio Team <noreply@myownradio.biz>";
            $flag       = "-fno-reply@myownradio.biz";
            
            $headers    = "Content-Type: text/html; charset=\"UTF-8\"\r\n";
            $headers   .= "From: {$sender}\r\n";

            $template   = new template("application/tmpl/reg.request.mail.tmpl");
            $message    = $template
                    ->addVariable("confirm", $confirm, true)
                    ->makeDocument();

            if (!mail($email->get(), $subject, $message, $headers, $flag))
            {
                throw new morException("Registration letter could not be delivered", 2501, null, "email");
            }
            
            misc::writeDebug("Message off");
        }

        echo json_encode(array(
            'status' => 1,
            'submit' => $submit
        ));
    }
    

    public function passwordRecoverRequest()
    {
        $input_value = new validSomething(application::post("email", null, REQ_STRING), "login");

        // Try to find account
        $account = $this->database->query_single_row("SELECT * FROM `r_users` WHERE `login` = :id OR `mail` = :id LIMIT 1", 
                array('id' => $input_value));

        if(is_null($account))
        {
            throw new validException("User with this email/login could not be found", "login");
        }

        // Generate letter
        $confirmLink = base64_encode(json_encode(array('login' => $account['login'], 'passwd' => $account['password'])));

        $subject = "myownradio.biz: Password reset";

        $headers  = "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $headers .= "From: myownradio.biz <no-reply@myownradio.biz>\r\n";

        $message = "<span style='font-size: 12pt;'>";
        $message .= "<b>Reset password on myownradio.biz</b><br><br>";
        $message .= "Hello, <b>${account['login']}</b>! We received a request to reset password on <b>myownradio.biz</b>. If you want to reset password, please visit the link below. If you did not make this request, or you changed your mind, you can ignore this request and continue using your existing password.<br><br>";
        $message .= "<a href='http://myownradio.biz/recover/$confirmLink'>Reset password</a><br>";
        $message .= "<br>";
        $message .= "Sincerely,<br>";
        $message .= "The MyOwnRadio Team";
        $message .= "</span>";

        if (mail($account['mail'], $subject, $message, $headers, "-fno-reply@myownradio.biz"))
        {
            echo misc::okJSON();
        }
        else
        {
            throw new validException("Unknown error", "login");
        }

    }
    
    
}

