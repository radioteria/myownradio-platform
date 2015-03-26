<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 12:47
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Defaults;
use Framework\Template;

class DoIndex implements Controller {
    public function doGet() {

        $description = "Create your own free web radio station for few minutes";
        $keywords = "music, radio, create, radiostation, webradio, listen, free, own";

        $metadata = new Template("frontend/meta.default.tmpl");
        $metadata->putObject([
            "title"         => Defaults::SITE_TITLE,
            "description"   => $description,
            "keywords"      => $keywords
        ]);

        $template = new Template("frontend/index.tmpl");
        $template->putObject([
            "title" => Defaults::SITE_TITLE,
            "metadata" => $metadata->render()
        ]);
        $template->display();
    }
} 