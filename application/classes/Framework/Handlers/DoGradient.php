<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Context;
use Framework\Controller;
use Framework\FileServer\FSFile;
use Framework\Services\DB\Query\SelectQuery;
use Objects\FileServer\FileServerFile;

class DoGradient implements Controller {
    public function doGet() {

        echo Context::getLimit(10);

//        $legacy = "./legacy";
//
//        $modules = (new SelectQuery("r_modules"))->fetchAll();
//        echo getcwd();
//        $keys = ["css", "js", "html", "tmpl", "post"];
//        foreach ($modules as $module) {
//
//            foreach ($keys as $key) {
//
//                if (strlen($module[$key]) > 0) {
//
//                    mkdir($legacy."/".$key, 0777, true);
//                    file_put_contents($legacy."/".$key."/".$module["name"].".".$key, $module[$key]);
//                }
//            }
//        }

    }

    public function doHead() {
        usleep(rand(500000, 5000000));
        http_response_code(404);
    }
} 