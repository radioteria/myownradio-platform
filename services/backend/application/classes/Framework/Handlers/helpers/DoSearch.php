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
use Framework\Services\HttpGet;
use Framework\Template;

class DoSearch implements Controller {
    public function doGet(HttpGet $get) {

        $pageTitle = "Search results on ".Defaults::SITE_TITLE;

        extract([
            "title" => $pageTitle,
            "assets" => json_decode(file_get_contents(INDEX_DIR . "/assets/assets-manifest.json"), true)
        ]);

        include BASE_DIR . "/application/tmpl/frontend/index.tmpl";

    }
} 
