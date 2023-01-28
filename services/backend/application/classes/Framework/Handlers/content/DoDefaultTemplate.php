<?php

namespace Framework\Handlers\content;

use Framework\ControllerImpl;
use Framework\Defaults;
use Framework\Services\CurrentRoute;
use Framework\Services\TwigTemplate;
use Framework\Template;

class DoDefaultTemplate extends ControllerImpl
{
    public function doGet(CurrentRoute $currentRoute)
    {
        $description = "Create your own free web radio station in a minutes";
        $keywords = "music, radio, create, radio station, web radio, listen, free, own";

        switch ($currentRoute) {
            case "streams":
                $pageTitle = "Popular radio stations on ";
                break;
            case "categories":
                $pageTitle = "Radio stations categories on ";
                break;
            case "bookmarks":
                $pageTitle = "Your bookmarks on ";
                break;
            default:
                $pageTitle = "";
        }


        $metadata = new Template("frontend/meta.default.tmpl");
        $metadata->putObject([
            "title" => $pageTitle . Defaults::SITE_TITLE,
            "description" => $description,
            "keywords" => $keywords
        ]);

        $environment = env('ENV', 'dev');
        $scripts = new Template("frontend/scripts.{$environment}.tmpl");

        extract([
            "title" => $pageTitle . Defaults::SITE_TITLE,
            "metadata" => $metadata->render(),
            "scripts" => $scripts->render(),
            "assets" => json_decode(file_get_contents(INDEX_DIR . "/assets/assets-manifest.json"), true)
        ]);

        include BASE_DIR . "/application/tmpl/frontend/index.tmpl";
    }
}
