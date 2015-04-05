<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 21:14
 */

namespace Framework\Controllers\helpers;


use Framework\Controller;
use Framework\Defaults;
use Framework\Services\HttpGet;
use Framework\Template;
use Objects\Category;

class DoCategory implements Controller {
    public function doGet(HttpGet $get) {

        $param = $get->getParameter("category");

        $param->then(function ($category) {

            Category::getByFilter("key", ["key:" => $category])->otherwise(function () {
                http_response_code(404);
            });

        });

        $param->otherwise(function () {
            http_response_code(404);
        });


        $description = "Create your own free web radio station in a minutes";
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