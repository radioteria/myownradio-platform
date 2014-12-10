<?php

class user
{

    private static $userData = NULL;
    private static $userRights = NULL;
    private static $userSubs = NULL;
    private static $uid;

    private $userContents = array();
    
    function __construct($uid)
    {
        $this->userContents = db::query_single_row("SELECT * FROM `r_users` WHERE `uid` = ?", array($uid));
    }
    
    function hasAvatar()
    {
        return $this->userContents['hasavatar'];
    }
    
    function getName()
    {
        return $this->userContents['name'];
    }
    
    function getPermalink()
    {
        return $this->userContents['permalink'];
    }
    
    function getInfo()
    {
        return $this->userContents['info'];
    }
    
    static function loginBySession()
    {
        if(application::getMethod() === "GET")
        {
            $token = session::get('authtoken');
        }
        else
        {
            $token = application::getHeader("My-Own-Token");
        }
        
        session::end();
        
        $data = db::query_single_row('SELECT a.`permanent`, a.`token`, b.`uid`, b.`login`, b.`name`, b.`rights` FROM `r_sessions` a LEFT JOIN `r_users` b ON a.`uid` = b.`uid` WHERE a.`token` = ?', array($token));
        
        if (!is_null($data))
        {
            self::$userData = $data;

            // Update user's last activity
            db::query_update('UPDATE `r_users` SET `last_visit_date` = ? WHERE `uid` = ?', array(time(), self::$userData['uid']));
            // Get user's persmissions/limits
            self::$userRights = db::query_single_row("SELECT * FROM `r_limitations` WHERE `level` = IFNULL((SELECT `plan` FROM `r_subscriptions` WHERE `uid` = ? AND `expire` > ? ORDER BY `id` DESC LIMIT 1), 0)", array(self::$userData['uid'], time()));
            // Get user's current subscription
            self::$userSubs = db::query_single_row("SELECT * FROM `r_subscriptions` WHERE `uid` = ? AND `expire` > ? ORDER BY `id` DESC LIMIT 1", array(self::$userData['uid'], time()));

        }
    }

    static function getUserByUid($uid)
    {
        return db::query_single_row('SELECT * FROM `r_users` WHERE `uid` = ?', array($uid));
    }
    
    static function getUserAvatar($uid)
    {
        return config::getSetting("content", "content_folder") . sprintf("/avatars/userpicture%d.png", $uid);
    }
    
    static function loginUser()
    {
        $user = application::post('login');
        $password = application::post('password');
        $passwd = md5($user . $password);
        $saveCookie = application::post('remember', 'off', REQ_STRING);
        
        $data = db::query_single_row('SELECT * FROM `r_users` WHERE `login` = ? AND `password` = ?', array($user, $passwd));

        if (!is_null($data))
        {
            if($saveCookie === "on")
            {
                session::init(true);
            }
            
            db::query_update('UPDATE `r_users` SET `last_visit_date` = ? WHERE `uid` = ?', array(time(), $data['uid']));

            // Generate token
            $token = self::createToken($data['uid'], application::getClient(), 
                    filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'), session::getID());
            
            session::set('authtoken', $token);

            return misc::outputJSON('LOGIN_SUCCESS');
        }
        return misc::outputJSON('LOGIN_FAILED');
    }
    
    static function getUserByCredentials($login, $password)
    {
        return db::query_single_row('SELECT * FROM `r_users` WHERE `login` = ? AND `password` = ?', array($login, $password));
    }
    
    static function checkAccount($login, $password)
    {
        $passwd = md5($login . $password);
        $data = db::query_single_col('SELECT COUNT(*) FROM `r_users` WHERE `login` = ? AND `password` = ?', array($login, $passwd));
        return (bool) $data;
    }
    
    static function changePassword($uid, $login, $password)
    {
        $passwd = md5($login . $password);
        if(db::query_update("UPDATE `r_users` SET `password` = ? WHERE `uid` = ?", array($passwd, $uid)))
        {
            return misc::outputJSON("SUCCESS");
        }
        else
        {
            return misc::outputJSON("UNCHANGED");
        }
    }


    
    static function logoutUser()
    {
        $token = session::get('authtoken');
        db::query_update("DELETE FROM `r_sessions` WHERE `token` = ?", array($token));
        session::remove('authtoken');
        session::end();
        return misc::outputJSON('LOGOUT_SUCCESS');
    }
    
    static function getUserByToken($token)
    {
        return db::query_single_row('SELECT a.`token`, b.`uid`, b.`login`, b.`name`, b.`rights`, b.`mail` FROM `r_sessions` a LEFT JOIN `r_users` b ON a.`uid` = b.`uid` WHERE a.`token` = ?', array($token));
    }

    static function getCurrentUserId()
    {
        return ! is_null(self::$userData['uid']) ? (int) self::$userData['uid'] : 0;
    }

    static function getCurrentUserName()
    {
        return ! is_null(self::$userData['login']) ? self::$userData['login'] : "Guest";
    }

    static function getPermanentLogin()
    {
        return ! is_null(self::$userData['permanent']) ? (int)self::$userData['permanent'] : -1;
    }

    static function getCurrentUserInfo()
    {
        return ! is_null(self::$userData['info']) ? self::$userData['info'] : "";
    }
    
    static function getCurrentUserPermalink()
    {
        return ! is_null(self::$userData['permalink']) ? self::$userData['permalink'] : "";
    }
    
    static function getCurrentUserToken()
    {
        return !is_null(self::$userData['token']) ? self::$userData['token'] : "";
    }

    static function getCurrentUserRights()
    {
        return !is_null(self::$userData['rights']) ? self::$userData['rights'] : 0;
    }
    
    static function userUploadLimit()
    {
        return (int) self::$userRights['upload_limit'] * 60000;
    }

    static function userStreamsMax()
    {
        return (int) self::$userRights['streams_max'];
    }

    static function userUploadLeft()
    {
        if (self::userUploadLimit() > 0)
        {
            $total_time = track::getTracksDuration(self::getCurrentUserId());
            return (self::userUploadLimit() - $total_time > 0) ? (self::userUploadLimit() - $total_time) : 0;
        }
        else
        {
            return false;
        }
    }

    static function userActivePlan()
    {
        return self::$userRights['level'];
    }

    static function userPlanExpire()
    {
        return ! is_null(self::$userSubs) ? self::$userSubs['expire'] : 0;
    }

}
