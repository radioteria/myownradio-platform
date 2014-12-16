<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 16:34
 */

namespace MVC\Controllers\api\v2\track;


use Model\Track;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoEdit extends Controller {

    public function doPost(HttpPost $post) {

        $id      = $post->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));

        $artist  = $post->getParameter("artist")->getOrElseEmpty();
        $title   = $post->getParameter("title")->getOrElseEmpty();
        $album   = $post->getParameter("album")->getOrElseEmpty();
        $trackNR = $post->getParameter("track_number")->getOrElseEmpty();
        $genre   = $post->getParameter("genre")->getOrElseEmpty();
        $date    = $post->getParameter("date")->getOrElseEmpty();

        $color   = $post->getParameter("color")->getOrElse(0);

        $track = Track::getInstance($id);

        $track->edit($artist, $title, $album, $trackNR, $genre, $date, $color);

    }

} 