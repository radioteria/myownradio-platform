<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 13:15
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use JSMin\JSMin;
use Tools\File;

class DoGetJavascriptModules implements Controller {
    public function doGet() {
        $sources = [
            "js/modules/ng-infinite-scroll.min.js",
            "js/modules/angular-post-fix.js",
            "js/modules/loading-bar.min.js",
            "js/modules/ngDialog.min.js",
            "js/modules/ng-context-menu.js",
            "js/modules/angulartics.min.js",
            "js/modules/angulartics-ga.min.js",
            "js/modules/angular-seo.js",
            "js/libs/sortable.js",
            "js/libs/angular-touch.js",

            "application/modules/main.ang.js",
            "application/modules/filters.js",
            "application/modules/context.js",

            "application/modules/site.js",
            "application/modules/tools/mor.tools.ang.js",
            "application/modules/tools/mor.tools.defaults.ang.js",
            "application/modules/tools/mor.tools.share.ang.js",

            "application/modules/account.js",
            "application/modules/player.js",
            "application/modules/catalog.js",
            "application/modules/search.js",
            "application/modules/profile.js",
            "application/modules/library.js",
            "application/modules/audioinfo.js",
            "application/modules/loader.js",
            "application/modules/track-action.js",
            "application/modules/popup.ang.js",
            "application/modules/mor.stream.scheduler.js",

            "js/libs/mortip.js"
        ];

        $modification_time = 0;
        foreach($sources as $source) {
            if (filemtime($source) > $modification_time) {
                $modification_time = filemtime($source);
            }
        }

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modification_time) {
            header('HTTP/1.1 304 Not Modified');
            die();
        } else {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $modification_time) . " GMT");
            header('Cache-Control: max-age=0');
        }

        ob_start("ob_gzhandler");

        header("Content-Type: text/javascript");

        foreach ($sources as $source) {
            echo JSMin::minify((new File($source))->getContents());
            //(new File($source))->show();
        }

    }
} 