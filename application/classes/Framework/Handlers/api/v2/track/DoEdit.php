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
use Framework\Services\Http\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;
use Tools\Optional\Transform;

class DoEdit implements Controller {
    /** @var UpdateQuery $query */
    private $query;
    public function doPost(HttpPost $post, JsonResponse $response, DBQuery $dbq,
                           AuthUserModel $user, InputValidator $validator) {

        $id         = $post->getOrError("track_id");

        $artist     = $post->get("artist");
        $title      = $post->get("title");
        $album      = $post->get("album");
        $number     = $post->get("track_number");
        $genre      = $post->get("genre");
        $date       = $post->get("date");
        $color      = $post->get("color_id");
        $cue        = $post->get("cue");
        $buy        = $post->get("buy");
        $sharable   = $post->get("can_be_shared")->map(Transform::$toBoolean);

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
        $sharable   ->then(function ($share)    { $this->query->set("can_be_shared", $share); });

        $this->query->update();

    }

} 