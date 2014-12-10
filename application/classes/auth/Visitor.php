<?php

class Visitor extends Model
{
    use Singleton;
    
    private $vendee = null;
    private $guest = array(
        'uid'             => 0,
        'mail'            => null,
        'login'           => null,
        'password'        => null,
        'name'            => "Guest",
        'info'            => null,
        'rights'          => 0,
        'register_date'   => null,
        'last_visit_date' => null,
        'hasavatar'       => 0,
        'authorized'      => 0
    );
    private $token = null;
    
    public function __construct()
    {
        parent::__construct();
        
        if (count(func_get_args()) === 1) 
        {
            $row = $this->database->query_single_row("SELECT * FROM `r_users` WHERE `uid` = ?", array(func_get_arg(0)));
            if($row !== null)
            {
                $this->vendee = $row;
                $this->vendee['authorized'] = 1;
                return;
            }
        }
        else if (count(func_get_args()) === 2)
        {
            $uLogin = new validLogin(func_get_arg(0));
            $uPassw = new validPassword(func_get_arg(1));

            $row = $this->database->query_single_row("SELECT * FROM `r_users` WHERE `login` = ? AND `password` = ?", array($uLogin, $uPassw));
            if($row !== null)
            {
                $this->vendee = $row;
                $this->vendee['authorized'] = 1;
                return;
            }
        }
        else
        {
            $this->getTokenFromSession();
            if ($this->token !== null) {
                $row = $this->database->query_single_row("SELECT * FROM `r_users` WHERE `uid` = ?", array($this->getIdBySessionToken()));
                if($row !== null)
                {
                    $this->vendee = $row;
                    $this->vendee['authorized'] = 1;
                    return;
                }
            }
        }
            
        $this->vendee = $this->guest;
     
    }
    
    public function getId()
    {
        return (int) $this->vendee['uid'];
    }
    
    public function getLogin()
    {
        return $this->vendee['login'];
    }
    
    public function getEmail()
    {
        return $this->vendee['mail'];
    }
    
    public function getToken()
    {
        return $this->token;
    }

    public function changePassword($password)
    {
        $new_password = md5($this->getLogin() . $password);
        $result = db::query_update("UPDATE `r_users` SET `password` = ? WHERE `uid` = ?", array($new_password, $this->getId()));
        if($result === 0)
        {
            throw new Exception("Password was not updated", "UNCHANGED");
        }
        return $this;
    }
    
    public function getStatus()
    {
        return array(
            'user_id' => (int) $this->vendee['uid'],
            'user_email' => $this->vendee['mail'],
            'user_login' => $this->vendee['login'],
            'user_name' => $this->vendee['name'],
            'user_reg_date' => date("d M, Y", $this->vendee['register_date']),
            'user_reg_date_unix' => (int) $this->vendee['register_date']
        );
    }
    
    protected function getTokenFromSession()
    {
        $this->token = (application::getMethod() === "GET") 
               ? session::get('authtoken') 
               : application::getHeader("My-Own-Token");
        
        session::end();
        
        return $this;
    }

    protected function getIdBySessionToken()
    {
        $result = $this->database->query_single_col('SELECT b.`uid` FROM `r_sessions` a LEFT JOIN `r_users` b ON a.`uid` = b.`uid` WHERE a.`token` = ?', array($this->token));
        
        return ($result === null) ? 0 : (int) $result;
    }
    
    public function isAuthorized() {
        return (boolean) $this->vendee['authorized'];
    }


    static function createToken($uid, $ip, $ua, $session_id)
    {
        do
        {
            $token = md5($uid . $ip . rand(1,1000000) . "tokenizer" . time());
        }
        while(db::query_single_col("SELECT COUNT(*) FROM `r_sessions` WHERE `token` = ?", array($token)) > 0);
        
        db::query_update("INSERT INTO `r_sessions` SET `uid` = ?, `ip` = ?, `token` = ?, `permanent` = 1, `authorized` = NOW(), `http_user_agent` = ?, `session_id` = ?, `expires` = NOW() + INTERVAL 1 YEAR", array(
            $uid, $ip, $token, $ua, $session_id
        ));
        
        return $token;
    }
}
