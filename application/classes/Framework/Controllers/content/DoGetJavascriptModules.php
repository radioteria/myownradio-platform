<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 13:15
 */

namespace Framework\Controllers\content;


use Framework\Controller;
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

        ob_start("ob_gzhandler");

        header("Content-Type: text/javascript");

        foreach ($sources as $source) {
            (new File($source))->show();
            echo "\n\n";
        }

    }
} 