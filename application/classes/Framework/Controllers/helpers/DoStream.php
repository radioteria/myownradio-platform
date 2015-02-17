<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.02.15
 * Time: 9:36
 */

namespace Framework\Controllers\helpers;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Template;
use REST\Streams;

class DoStream implements Controller {
    public function doGet(HttpGet $get, Streams $streams) {

        $id = $get->getRequired("id");

        try {

            $stream = $streams->getOneStream($id);

            $template = new Template("application/tmpl/fb.stream.tmpl");
            $template->putObject($stream);
            $template->addVariable("title", "myownradio.biz - your own web radio station");

            echo $template->makeDocument();

        } catch (ControllerException $exception) {
            http_response_code(404);
            http_redirect("http://myownradio.biz/404");
        }

    }
} 