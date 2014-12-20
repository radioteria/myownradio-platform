<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 16:34
 */

namespace Framework\Controllers\api\v2\track;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Model\TrackModel;

class DoEdit extends Controller {

    public function doPost(HttpPost $post) {

        $id      = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        $artist  = $post->getParameter("artist")->getOrElseEmpty();
        $title   = $post->getParameter("title")->getOrElseEmpty();
        $album   = $post->getParameter("album")->getOrElseEmpty();
        $number  = $post->getParameter("track_number")->getOrElseEmpty();
        $genre   = $post->getParameter("genre")->getOrElseEmpty();
        $date    = $post->getParameter("date")->getOrElseEmpty();

        $color   = $post->getParameter("color")->getOrElse(0);

        TrackModel::getInstance($id)->edit($artist, $title, $album, $number, $genre, $date, $color);

    }

} 