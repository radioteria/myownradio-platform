<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 20.03.2015
 * Time: 19:03
 */

namespace Framework\Handlers\content;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpGet;
use Framework\Services\SomeClass;
use Framework\Services\TwigTemplate;
use Objects\Track;

class DoTrackExtraInfo implements Controller {
    public function doGet(HttpGet $get, AuthUserModel $user, DBQuery $query, TwigTemplate $template) {

        $id = $get->getRequired("id");

        /** @var Track $track */
        $track = Track::getByID($id)->getOrElseNull();

        if ($track === null) {

            echo "Track not found";

        } else if ($track->getUserID() !== $user->getID()) {

            echo "No permission";

        } else {

            $streams = $query->selectFrom("r_streams")
                ->innerJoin("r_link", "r_link.stream_id = r_streams.sid")
                ->where("r_link.track_id", $id)->addGroupBy("r_streams.sid")
                ->select("r_streams.*, COUNT(*) as times")
                ->fetchAll("sid");

            $template->displayTemplate("track.extra.info.tmpl", [
                "track" => $track->jsonSerialize(),
                "appears" => $streams
            ]);

        }


    }
}