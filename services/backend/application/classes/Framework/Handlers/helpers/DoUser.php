<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 14:13
 */

namespace Framework\Handlers\helpers;


use Framework\Controller;
use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Router;
use Framework\Services\HttpGet;
use Framework\Template;
use Objects\User;
use REST\Users;

class DoUser implements Controller {
    public function doGet(HttpGet $get, Users $users) {

        $id = $get->getRequired("id");

        try {
            /** @var User $user */
            $user = User::getByFilter("FIND_BY_KEY", [":key" => $id])->getOrElseThrow(
                ControllerException::noUser($id)
            );

            $title = $user->getName() ? $user->getName() : $user->getLogin();

            $pageTitle = $title."'s radio channels on ".Defaults::SITE_TITLE;

            $metadata = new Template("frontend/meta.user.tmpl");
            $metadata->putObject([
                "title"         => $pageTitle,
                "description"   => $user->getInfo(),
                "keywords"      => "",
                "url"           => "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                "image"         => $user->getAvatarUrl(),
                "name"          => $title
            ]);

            $template = new Template("frontend/index.tmpl");
            $template->putObject([
                "title" => $pageTitle,
                "metadata" => $metadata->render()
            ]);

            $template->display();

        } catch (ControllerException $exception) {

            http_response_code(404);
            Router::getInstance()->callRoute("content\\DoDefaultTemplate");

        }

    }
} 