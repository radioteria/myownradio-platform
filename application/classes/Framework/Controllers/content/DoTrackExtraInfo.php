<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 20.03.2015
 * Time: 19:03
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpGet;
use Framework\Template;
use Objects\Track;

class DoTrackExtraInfo implements Controller {
    public function doGet(HttpGet $get, AuthUserModel $user, DBQuery $query) {

        $id = $get->getRequired("id");

        /** @var Track $track */
        $track = Track::getByID($id)->getOrElseNull();

        if ($track === null) {
            // Track not found
        } else if ($track->getUserID() !== $user->getID()) {
            throw ControllerException::noPermission();
        } else {
            $streams = $query->selectFrom("r_streams")
                ->innerJoin("r_link", "r_link.stream_id = r_streams.sid")
                ->where("r_link.track_id", $id)->addGroupBy("r_streams.sid")
                ->select("r_streams.*, COUNT(*) as times")
                ->fetchAll("sid");

            $loader = new \Twig_Loader_Filesystem("application/tmpl");

            $twig = new \Twig_Environment($loader, []);

//            $twig->addFilter("json", new \Twig_SimpleFilter("json", function ($src) {
//                return json_encode($src);
//            }));

            $twig->loadTemplate("track.extra.info.tmpl")->display([
                    "track" => $track->jsonSerialize(),
                    "appears" => $streams
            ]);

        }


    }
}