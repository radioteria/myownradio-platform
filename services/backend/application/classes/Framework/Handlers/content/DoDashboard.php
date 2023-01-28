<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 13:31
 */

namespace Framework\Handlers\content;


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

        extract([
            "title" => "Your Dashboard on MyOwnRadio - Your own web radio station",
            "assets" => json_decode(file_get_contents(INDEX_DIR . "/assets/assets-manifest.json"), true)
        ]);

        include BASE_DIR . "/application/tmpl/frontend/index.tmpl";

    }
} 
