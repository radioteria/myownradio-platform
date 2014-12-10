<?php
class Registrator {

    /**
     * @param validLogin $login user login
     * @param validPassword $passw1 user password
     * @param validPassword $passw2 repeat user password
     * @param $name user name
     * @param $info information about user
     * @param $code verification code
     * @return json status
     * @throws morException
     *
     * This function used to verify code emailed to user and complete user registration
     */
    public static function codeCheck(validLogin $login, validPassword $passw1,
                                     validPassword $passw2, $name, $info, $code) {
        
        $database = Database::getInstance();

        if ($passw1->get() != $passw2->get()) {
            throw new morException("Entered passwords did not match", 3003, null, "password");
        }

        try {
            $codeArray = json_decode(base64_decode($code), true);
        } catch (Exception $ex) {
            throw new morException("Incorrect code", 3004, null);
        }

        if (md5($codeArray['email'] . "@myownradio.biz@" . $codeArray['email']) != $codeArray['code']) {
            throw new morException("Incorrect code", 3004, null);
        }

        if (db::query_single_col("SELECT COUNT(`uid`) FROM `r_users` WHERE `mail` = ?", array($codeArray['email'])) > 0) {
            throw new morException("User with this email already exists", 3005, null);
        }

        $inserted = $database->query_update(
            "INSERT INTO `r_users` SET `mail` = ?, `login` = ?, `password` = ?, `name` = ?, `info` = ?, `register_date` = ?",
                array($codeArray['email'], $login, md5($login . $passw1), $name, $info, time()));

        if ($inserted > 0) {
            $uid = $database->lastInsertId();
            misc::writeDebug(sprintf("New user registered: id=%d, mail=%s", $uid, $codeArray['email']));
            self::createUserDirectory($uid);
            return misc::okJSON();
        } else {
            throw new morException("User could not be registered!", 3006, null);
        }
    }

    /**
     * @param validSomething $code (verification code)
     * @param validPassword $password (new password)
     * @return json status
     * @throws morException
     *
     * This function is used for user's password changing
     */
    public static function checkCodePassword(validSomething $code, validPassword $password) {

        try
        {
            $json = json_decode(base64_decode($code), true);
        } catch (Exception $ex) {
            throw new morException("Incorrect code");
        }

        $login = $json['login'];
        $passw = $json['passwd'];

        $account = new Visitor($login, $passw);

        if (!$account->isAuthorized())
        {
            throw new morException("Code expired");
        }

        session::remove('authtoken');
        
        $account->changePassword($password);

        return misc::okJSON();

    }

    /**
     * @param $user_id
     * @return bool
     */
    private static function createUserDirectory($user_id)
    {
        $new_path = sprintf("%s/ui_%d", config::getSetting("content", "content_folder"), $user_id);
        if (!is_dir($new_path))
        {
            return mkdir($new_path, NEW_DIR_RIGHTS, true);
        }
        return false;
    }

}
