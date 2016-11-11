<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 13:15
 */

namespace Framework\Handlers\content;

use Framework\Controller;
use Tools\File;

class DoGetJavascriptModules implements Controller
{
    public function doGet()
    {
        $sources = [
            "public/js/modules/ng-infinite-scroll.min.js",
            "public/js/modules/angular-post-fix.js",
            "public/js/modules/loading-bar.min.js",
            "public/js/modules/ngDialog.min.js",
            "public/js/modules/ng-context-menu.js",
            "public/js/modules/angulartics.min.js",
            "public/js/modules/angulartics-ga.min.js",
            "public/js/modules/angular-seo.js",
            "public/js/libs/sortable.js",
            "public/js/libs/angular-touch.js",

            "public/js/mor-modules/main.ang.js",
            "public/js/mor-modules/filters.js",
            "public/js/mor-modules/context.js",

            "public/js/mor-modules/site.js",
            "public/js/mor-modules/tools/mor.tools.ang.js",
            "public/js/mor-modules/tools/mor.tools.defaults.ang.js",
            "public/js/mor-modules/tools/mor.tools.share.ang.js",
            "public/js/mor-modules/tools/mor.tools.stats.ang.js",
            "public/js/mor-modules/tools/mor.tools.image.ang.js",

            "public/js/mor-modules/account.js",
            "public/js/mor-modules/player.js",
            "public/js/mor-modules/catalog.js",
            "public/js/mor-modules/search.js",
            "public/js/mor-modules/profile.js",
            "public/js/mor-modules/library.js",
            "public/js/mor-modules/audioinfo.js",
            "public/js/mor-modules/loader.js",
            "public/js/mor-modules/track-action.js",
            "public/js/mor-modules/popup.ang.js",
            "public/js/mor-modules/mor.stream.scheduler.js",

            "public/js/mor-modules/api/api.core.js",
            "public/js/mor-modules/api/api.channels.js",
            "public/js/mor-modules/api/api.tracks.js",
            "public/js/mor-modules/api/api.bookmarks.js",
            "public/js/mor-modules/api/api.schedule.js",
            "public/js/mor-modules/api/api.events.js",

            "public/js/mor-modules/filters/filter.object.js",

            "public/js/mor-modules/controllers/controllers.channels.js",
            "public/js/mor-modules/controllers/controllers.tracks.js",

            "public/js/mor-modules/directives/directives.player.js",

            "public/js/mor-modules/ui/ui.hashtags.js",

            "public/js/libs/mortip.js"
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
            //echo JSMinPlus::minify((new File($source))->getContents());
            (new File($source))->show();
            echo "\n\n";
        }

    }
}
