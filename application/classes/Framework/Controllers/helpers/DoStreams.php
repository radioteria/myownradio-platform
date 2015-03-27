<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.03.15
 * Time: 19:57
 */

namespace Framework\Controllers\helpers;

use Framework\Controller;
use Framework\Defaults;
use Framework\Template;

class DoStreams implements Controller {
    public function doGet() {
        $description = "Create your own free web radio station in a minutes";
        $keywords = "music, radio, create, radio station, web radio, listen, free, own";

        $metadata = new Template("frontend/meta.default.tmpl");
        $metadata->putObject([
            "title"         => "Radio channels on ".Defaults::SITE_TITLE,
            "description"   => $description,
            "keywords"      => $keywords
        ]);

        $template = new Template("frontend/index.tmpl");
        $template->putObject([
            "title" => "Radio channels on ".Defaults::SITE_TITLE,
            "metadata" => $metadata->render()
        ]);
        $template->display();
    }
} 