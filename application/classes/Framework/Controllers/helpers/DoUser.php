<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 14:13
 */

namespace Framework\Controllers\helpers;


use Framework\Controller;
use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Template;
use Framework\View\Errors\View404Exception;
use REST\Users;

class DoUser implements Controller {
    public function doGet(HttpGet $get, Users $users) {

        $id = $get->getRequired("id");

        try {

            $user = $users->getUserByID($id);

            $pageTitle = $user["name"]."'s radio channels on ".Defaults::SITE_TITLE;

            $metadata = new Template("frontend/meta.user.tmpl");
            $metadata->putObject([
                "title"         => $pageTitle,
                "description"   => $user["info"],
                "keywords"      => "",
                "url"           => "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                "image"         => $user["avatar_url"] ? "https:".$user["avatar_url"] : "",
                "name"          => $user["name"]
            ]);

            $template = new Template("frontend/index.tmpl");
            $template->putObject([
                "title" => $pageTitle,
                "metadata" => $metadata->render()
            ]);

            $template->display();

        } catch (ControllerException $exception) {
            throw new View404Exception();
        }

    }
} 