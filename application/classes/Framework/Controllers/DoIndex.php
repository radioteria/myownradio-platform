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
        $template = new Template("frontend/index.tmpl");
        $template->putObject([
            "title" => Defaults::SITE_TITLE,
            "metadata" =>
                '<meta name="description" content="Create your own free web radio station for few minutes!">'.
                '<meta name="keywords" content="music, radio, create, radiostation, webradio, listen, free, own">'
        ]);
        $template->display();
    }
} 