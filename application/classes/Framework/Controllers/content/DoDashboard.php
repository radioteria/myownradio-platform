<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 13:31
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Template;

class DoDashboard implements Controller {
    public function doGet() {

        if (AuthUserModel::getAuthorizedUserID() === null) {
            header("HTTP/1.1 403 Forbidden");
            header("Location: /login/");
            return;
        }

        $template = new Template("frontend/index.tmpl");
        $template->putObject([
            "title" => "Your Dashboard on MyOwnRadio - Your own web radio station"
        ]);
        $template->display();

    }
} 