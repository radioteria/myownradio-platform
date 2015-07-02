<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:32
 */

namespace Framework\Models;


use Business\Fields\Code;
use Business\Fields\Password;
use Business\Forms\LoginForm;
use Business\Forms\SignUpCompleteForm;
use Framework\Events\RegistrationSuccessfulPublisher;
use Framework\Exceptions\Auth\IncorrectPasswordException;
use Framework\Exceptions\Auth\NoUserByLoginException;
use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\InsertQuery;
use Framework\Services\HttpRequest;
use Framework\Services\HttpSession;
use Objects\User;
use Tools\Common;
use Tools\Folders;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class UsersModel
 * @package Framework\Models
 * @localized 21.05.2015
 */
class UsersModel implements SingletonInterface, Injectable {

    use Singleton;

    const CLIENT_ID_LENGTH = 8;

    /**
     * @param string $login
     * @param string $password
     * @param bool $save
     * @return UserModel
     * @throws IncorrectPasswordException
     */
    public function authorizeByLoginPassword($login, $password, $save = false) {

        $session = HttpSession::getInstance();

        $pwd = new Password($password);

        // Try to find user specified by login or email
        $user = DBQuery::getInstance()
            ->selectFrom("r_users")
            ->where("login = :key OR mail = :key", [":key" => $login])
            ->fetchOneRow()
            ->getOrThrow(NoUserByLoginException::className(), $login);

        if (!$pwd->matches($user["password"])) {
            throw new IncorrectPasswordException();
        }

        $token = self::createToken($user["uid"], $session->getSessionId());

        $session->set("TOKEN", $token);

        return UserModel::getInstance($user["uid"]);

    }

    /**
     * @param LoginForm $form
     * @throws IncorrectPasswordException | NoUserByLoginException
     */
    public function authorizeByLoginForm(LoginForm $form) {

        $password = new Password($form->getPassword());

        /**
         * @var User $user
         */
        $user = User::getByFilter("login = :key OR mail = :key", [":key" => $form->getLogin()])
            ->getOrThrow(NoUserByLoginException::class, $form->getLogin());

        if (!$password->matches($user->getPassword())) {
            throw new IncorrectPasswordException();
        }

        $session = HttpSession::getInstance();

        self::logout();

        $token = self::createToken($user->getId(), $session->getSessionId());

        $session->set("TOKEN", $token);

    }

    /**
     * @param $userId
     * @param $sessionId
     * @return string
     */
    private function createToken($userId, $sessionId) {

        $token = Database::doInConnection(function (Database $db) use ($userId, $sessionId) {

            $request = HttpRequest::getInstance();

            $clientAddress      = $request->getRemoteAddress();
            $clientUserAgent    = $request->getHttpUserAgent()->getOrElse("None");

            do $token = md5($userId . $clientAddress . rand(1, 1000000) . "tokenizer" . time());
            while ($db->fetchRowCount("FROM r_sessions WHERE token = ?", [$token]) > 0);

            $query = new InsertQuery("r_sessions");
            $query->values("uid", $userId);
            $query->values("ip", $clientAddress);
            $query->values("token", $token);
            $query->values("permanent", 1);
            $query->values("client_id", Common::generateUniqueId(self::CLIENT_ID_LENGTH));
            $query->values("authorized = NOW()");
            $query->values("http_user_agent", $clientUserAgent);
            $query->values("session_id", $sessionId);
            $query->values("expires = NOW() + INTERVAL 1 YEAR");

            $db->executeUpdate($query);

            return $token;

        });

        return $token;

    }

    /**
     * @return void
     */
    public function logout() {

        HttpSession::getInstance()->get("TOKEN")->then(function ($token) {
            DBQuery::getInstance()->deleteFrom("r_sessions", "token", $token)->update();
        });

    }

    /**
     * @param $id
     * @return mixed
     */
    public function authorizeById($id) {

        $session = HttpSession::getInstance();

        $user = Database::doInConnection(function (Database $db) use ($id) {
            return $db->fetchOneRow("SELECT * FROM r_users WHERE uid = ?", [$id])
                ->getOrThrow(NoUserByLoginException::className(), $id);
        });

        $token = self::createToken($user["uid"], $session->getSessionId());

        $session->set("TOKEN", $token);

        return UserModel::getInstance($id);

    }

    /**
     * @param SignUpCompleteForm $form
     * @return UserModel
     * @throws ControllerException
     * @internal param $code
     * @internal param $login
     * @internal param $password
     * @internal param $name
     * @internal param $info
     * @internal param $permalink
     * @internal param $country
     */
    public function completeRegistration(SignUpCompleteForm $form) {

        $pwd = new Password($form->getPassword());

        $newUser = new User();

        $newUser->setEmail($form->getEmail());
        $newUser->setLogin($form->getLogin());
        $newUser->setPassword($pwd->hash());
        $newUser->setName($form->getName());
        $newUser->setInfo($form->getInfo());
        $newUser->setPermalink($form->getPermalink());
        $newUser->setRights(1);
        $newUser->setRegistrationDate(time());
        $newUser->setCountryId($form->getCountryId());

        $newUser->save();

        // Generate Stream Cover
        $random = Common::generateUniqueId();
        $newImageFile = sprintf("avatar%05d_%s.%s", $newUser->getId(), $random, "png");
        $newImagePath = Folders::getInstance()->genAvatarPath($newImageFile);

        $newUser->setAvatar($newImageFile);
        $newUser->save();

        Common::createTemporaryImage($newImagePath);

        // todo: fix this
        RegistrationSuccessfulPublisher::getInstance()->publish($newUser);

        LettersModel::sendRegistrationCompleted($newUser->getEmail());

        return new UserModel($newUser->getId());

    }

    /**
     * @param $base64
     * @return mixed
     * @throws \Framework\Exceptions\ControllerException
     */
    public function parseRegistrationCode($base64) {

        $code = new Code($base64);
        $code->hasOrError("email", "code");

        return $code;

    }

    /**
     * @param $code
     * @param $password
     */
    public function completePasswordReset($code, $password) {

        $credentials = self::parseResetPasswordCode($code);
        $pwd = new Password($password);

        $user = new UserModel($credentials->getLogin(), $credentials->getPassword());

        $user->changePasswordNow($pwd);

    }

    /**
     * @param $base64
     * @return Code
     * @throws \Framework\Exceptions\ControllerException
     */
    public function parseResetPasswordCode($base64) {

        $code = new Code($base64);

        $code->hasOrError("login", "password");

        Database::doInConnection(function (Database $db) use ($code) {

            $query = $db->getDBQuery()->selectFrom("r_users");
            $query->where("login", $code->getLogin());
            $query->where("password", $code->getPassword());
            $query->select("*");

            $db->fetchOneRow($query)->getOrThrow(
                ControllerException::tr("ERROR_CODE_NOT_ACTUAL")
            );

        });

        return $code;

    }

}