<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 14:30
 */

namespace Framework\Handlers\helpers;


use Framework\Controller;
use Framework\Defaults;
use Framework\Template;

class DoSearch implements Controller {
    public function doGet() {

        $pageTitle = "Search results on " . Defaults::SITE_TITLE;

        $template = new Template("frontend/index.tmpl");
        $template->putObject([
            "title" => $pageTitle,
        ]);

        $template->display();

    }
} 