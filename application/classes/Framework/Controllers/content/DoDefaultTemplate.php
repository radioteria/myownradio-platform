<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 13:43
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Defaults;
use Framework\Services\CurrentRoute;
use Framework\Template;

class DoDefaultTemplate implements Controller {
    public function doGet(CurrentRoute $currentRoute) {

        $description = "Create your own free web radio station in a minutes";
        $keywords = "music, radio, create, radio station, web radio, listen, free, own";

        switch ($currentRoute) {
            case "streams":
                $pageTitle = "Radio channels on ";
                break;
            case "bookmarks":
                $pageTitle = "Your bookmarks on ";
                break;
            default:
                $pageTitle = "";
        }

        $metadata = new Template("frontend/meta.default.tmpl");
        $metadata->putObject([
            "title"         => $pageTitle.Defaults::SITE_TITLE,
            "description"   => $description,
            "keywords"      => $keywords
        ]);

        $template = new Template("frontend/index.tmpl");
        $template->putObject([
            "title" => $pageTitle.Defaults::SITE_TITLE,
            "metadata" => $metadata->render()
        ]);
        $template->display();

    }
} 