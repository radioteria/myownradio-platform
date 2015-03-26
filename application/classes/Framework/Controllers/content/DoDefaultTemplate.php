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
use Framework\Template;

class DoDefaultTemplate implements Controller {
    public function doGet() {

        $description = "Create your own free web radio station for few minutes";
        $keywords = "music, radio, create, radio station, web radio, listen, free, own";

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