<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 15:20
 */

namespace Model\Traits;

use Model\ActiveRecords\StreamTrack;

/**
 * Class StreamControl
 * @package Model\Traits
 */
trait StreamControl {

    public function scPlayNext() {

        $this->getPlayingTrack()->then(function (StreamTrack $track) {

            $next = ($track->getTrackOrder() + 1) % count($this);

            $this->_getPlaylistTrack("b.t_order = ? AND b.stream_id = ?", [$next, $this->key])

                ->then(function (StreamTrack $track) {

                    $this->_setCurrentTrack($track, 0, true);

                });

        });


    }

    public function scPlayPrevious() {

        $this->getPlayingTrack()->then(function (StreamTrack $track) {

            if ($track->getTrackOrder() < 2) {
                $prev = count($this);
            } else {
                $prev = $track->getTrackOrder() - 1;
            }

            $this->_getPlaylistTrack("b.t_order = ? AND b.stream_id = ?", [$prev, $this->key])

                ->then(function (StreamTrack $track) {

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

} 