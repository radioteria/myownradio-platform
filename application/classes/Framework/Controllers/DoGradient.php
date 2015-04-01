<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\DB\Query\UpdateQuery;
use Framework\Services\Locale\L10n;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(30);

        // This will remove all copies of audio tracks if users have ones
        $items = (new SelectQuery("TRACKS_COPIES"))->fetchAll();

        foreach ($items as $item) {
            $track_ids = explode(",", $item["tids"]);
            $source = array_shift($track_ids);

            (new UpdateQuery("r_link"))->set("track_id", $source)->where("track_id", $track_ids)->update();
            (new DeleteQuery("r_tracks"))->where("tid", $track_ids)->update();
        }

    }
} 