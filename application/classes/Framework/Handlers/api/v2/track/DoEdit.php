<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 16:34
 */

namespace Framework\Handlers\api\v2\track;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\UpdateQuery;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoEdit implements Controller {
    /** @var UpdateQuery $query */
    private $query;
    public function doPost(HttpPost $post, JsonResponse $response, DBQuery $dbq, AuthUserModel $user,
                           InputValidator $validator) {

        $id         = $post->getRequired("track_id");

        $artist     = $post->getParameter("artist");
        $title      = $post->getParameter("title");
        $album      = $post->getParameter("album");
        $number     = $post->getParameter("track_number");
        $genre      = $post->getParameter("genre");
        $date       = $post->getParameter("date");
        $color      = $post->getParameter("color_id");
        $cue        = $post->getParameter("cue");
        $buy        = $post->getParameter("buy");
        $sharable   = $post->getParameter("can_be_shared");

        $validator->validateTracksList($id);

        $this->query = $dbq->updateTable("r_tracks")
                    ->where("uid", $user->getID())
                    ->where("tid", explode(",", $id));

        $artist     ->then(function ($artist)   { $this->query->set("artist", $artist); });
        $title      ->then(function ($title)    { $this->query->set("title", $title); });
        $album      ->then(function ($album)    { $this->query->set("album", $album); });
        $number     ->then(function ($number)   { $this->query->set("track_number", $number); });
        $genre      ->then(function ($genre)    { $this->query->set("genre", $genre); });
        $date       ->then(function ($date)     { $this->query->set("date", $date); });
        $color      ->then(function ($color)    { $this->query->set("color", $color); });
        $cue        ->then(function ($cue)      { $this->query->set("cue", $cue); });
        $buy        ->then(function ($buy)      { $this->query->set("buy", $buy); });
        $sharable   ->then(function ($share)    { $this->query->set("can_be_shared", (bool) $share); });

        $this->query->update();

    }

} 