<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.02.15
 * Time: 9:36
 */

namespace Framework\Controllers\helpers;


use Framework\Controller;
use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Template;
use Framework\View\Errors\View404Exception;
use REST\Streams;

class DoStream implements Controller {
    public function doGet(HttpGet $get, Streams $streams) {

        $id = $get->getRequired("id");

        try {

            $stream = $streams->getOneStream($id);

            $pageTitle = $stream["name"]." on ".Defaults::SITE_TITLE;

            $metadata = new Template("frontend/meta.stream.tmpl");
            $metadata->putObject([
                "title"         => $pageTitle,
                "description"   => $stream["info"],
                "keywords"      => $stream["hashtags"],
                "image"         => $stream["cover_url"],
                "url"           => "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                "stream_id"     => $stream["sid"]
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