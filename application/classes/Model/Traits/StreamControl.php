<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 15:20
 */

namespace Model\Traits;

use Objects\PlaylistTrack;
use Objects\Stream;
use Tools\System;

/**
 * Class StreamControl
 * @package Model\Traits
 */
trait StreamControl {

    public function scPlayNext() {

        $this->getPlayingTrack()->then(function (PlaylistTrack $track) {

            $next = ($track->getTrackOrder() + 1) % count($this);

            $this->_getPlaylistTrack("b.t_order = ? AND b.stream_id = ?", [$next, $this->key])

                ->then(function (PlaylistTrack $track) {

                    $this->_setCurrentTrack($track, 0, true);

                });

        });


    }

    public function scPlayPrevious() {

        $this->getPlayingTrack()->then(function (PlaylistTrack $track) {

            if ($track->getTrackOrder() < 2) {
                $prev = count($this);
            } else {
                $prev = $track->getTrackOrder() - 1;
            }

            $this->_getPlaylistTrack("b.t_order = ? AND b.stream_id = ?", [$prev, $this->key])

                ->then(function (PlaylistTrack $track) {

                    $this->_setCurrentTrack($track, 0, true);

                });

        });

    }

    public function scPlayRandom() {

        $this->_getRandomTrack()
            ->then(function ($track) {
                $this->_setCurrentTrack($track, 0, true);
            });

    }

    public function scStop() {

        Stream::getByID($this->key)
            ->then(function ($stream) {
                /** @var Stream $stream */
                $stream->setStartedFrom(null);
                $stream->setStarted(null);
                $stream->setStatus(0);
                $stream->save();
            });

        $this->notifyStreamers();

    }

    public function scPlay() {

        Stream::getByID($this->key)
            ->then(function ($stream) {
                /** @var Stream $stream */
                $stream->setStartedFrom(0);
                $stream->setStarted(System::time());
                $stream->setStatus(1);
                $stream->save();
            });

        $this->notifyStreamers();

    }

} 