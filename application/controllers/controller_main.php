<?php

class get_controller extends controller {

    public function channel() {
        header("Content-Type: text/html; charset=utf-8");
        $template = new Template("application/tmpl/mor.skeleton.tmpl");
        echo $template->makeDocument();
    }

    public function commonscript() {
        header("Content-Type: text/javascript");
        module::printModulesJavaScript();
    }

    public function commoncss() {
        header("Content-Type: text/css");
        module::printModulesCSS();
    }

    /* TODO: Remove */
    public function login() {
        if (user::getCurrentUserId() === 0) {
            echo module::getModule("page.us.login");
        } else {
            header("HTTP/1.1 302 Moved Temporarily");
            header("Location: /radiomanager");
        }
    }

    /* TODO: Remove */
    public function logout() {
        user::logoutUser();
        header("Location: /login");
    }

    public function recoverSession() {

        $code = application::get("code", null, REQ_STRING);

        if (is_null($code)) {
            exit(module::getModule("page.us.static.expiredcode"));
        }

        try {
            $json = json_decode(base64_decode($code), true);
        } catch (Exception $ex) {
            exit(module::getModule("page.us.static.expiredcode"));
        }

        $login = $json['login'];
        $passw = $json['passwd'];

        $account = user::getUserByCredentials($login, $passw);

        if (is_null($account)) {
            exit(module::getModule("page.us.static.expiredcode"));
        }

        echo module::getModule("page.us.password");


    }

    public function confirm() {
        $code = application::get('code');

        $database = Database::getInstance();

        try {
            $codeArray = json_decode(base64_decode($code), true);
        } catch (Exception $ex) {
            echo module::getModule("us.error.regcode");
            return;
        }

        if ($database->query_single_col("SELECT COUNT(`uid`) FROM `r_users` WHERE `mail` = ?", array($codeArray['email'])) > 0) {
            echo module::getModule("us.error.confirm");
            return;
        }

        if (md5($codeArray['email'] . "@myownradio.biz@" . $codeArray['email']) != $codeArray['code']) {
            echo module::getModule("us.error.regcode");
            return;
        }

        echo module::getModule("page.us.signup2");
    }

    public function stream() {
        $stream_id = application::get("stream_id", NULL);

        try {
            $stream = application::singular('stream', $stream_id);
        } catch (Exception $ex) {
            exit(module::getModule("page.us.static.nostream"));
        }

        if ($stream->getState() !== 1) {
            exit(module::getModule("page.us.static.nostream"));
        }

        echo module::getModule("page.us.player", array(), array('stream_id' => $stream->getStreamId()));

    }

    public function larize() {
        echo module::getModule("larize");
    }

    public function module() {
        $module = application::get("name", NULL, REQ_STRING);
        $section = application::get("type", NULL, REQ_STRING);

        $sections = array( // content-type, cacheable, executable, section
            'html' => array('text/html', false, false, 'html'),
            'css' => array('text/css', true, false, 'css'),
            'js' => array('text/javascript', false, false, 'js'),
            'tmpl' => array('text/x-jquery-tmpl', true, false, 'tmpl'),
            'exec' => array('text/html', false, true, 'html')
        );

        if (!module::moduleExists($module)) {
            header("HTTP/1.1 404 Not Found");
            exit("<h1>File not found</h1>");
        }

        $data = module::fetchModule($module);

        if (!isset($data[$sections[$section][3]])) {
            header("HTTP/1.1 404 Not Found");
            exit("<h1>File not found</h1>");
        }

        if ($sections[$section][1] == true) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $data['unixmtime']) . " GMT");
            header('Cache-Control: max-age=0');

            /* Проверим не кэшировано ли изображение на стороне клиента */
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $data['unixmtime']) {
                header($_SERVER["SERVER_PROTOCOL"] . ' 304 Not Modified');
                die();
            }
        }

        header("Content-Type: " . $sections[$section][0]);

        if ($sections[$section][2]) {
            echo module::getModule($module, array(), application::getAll());
        } else {
            echo $data[$sections[$section][3]];
        }
    }
}

class post_controller extends controller {
    public function larize() {
        echo module::getModule("larize");
    }

    public function login() {
        echo user::loginUser();
    }

    public function logout() {
        echo user::logoutUser();
    }

    public function recoverSession() {
        $code = new validSomething(application::get("code", null, REQ_STRING));
        $password1 = new validPassword(application::post("password1", null, REQ_STRING), "password1");
        $password2 = new validPassword(application::post("password2", null, REQ_STRING), "password2");

        if ($password1->get() !== $password2->get()) {
            throw new validException("Passwords mismatch", "password2");
        }

        echo Registrator::checkCodePassword($code, $password1);
    }

    public function confirm() {
        $login = new validLogin(application::post('login'));
        $passw1 = new validPassword(application::post('password1'));
        $passw2 = new validPassword(application::post('password2'));

        $code = application::get('code');

        $name = application::post('name');
        $info = application::post('info');

        echo Registrator::codeCheck($login, $passw1, $passw2, $name, $info, $code);
    }

    public function streamStatus() {
        $stream_id = application::post('stream_id', NULL, REQ_STRING);
        $stream_sync = application::post('radio_sync', false, REQ_INT);

        try {
            application::singular('stream', $stream_id);
        } catch (Exception $ex) {
            echo misc::outputJSON("NO_STREAM");
            return;
        }


        echo json_encode(application::singular('stream', $stream_id)->getStreamStatus($stream_sync));

    }

}