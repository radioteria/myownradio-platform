<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 15:20
 */

namespace Framework\Models\Traits;

use Framework\Exceptions\ControllerException;
use Objects\StreamTrack;
use Objects\Stream;
use Tools\System;

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

    /**
     * @param $uniqueID
     * @param int $offset
     * @param bool $notify
     * @return $this
     */
    public function scPlayByUniqueID($uniqueID, $offset = 0, $notify = true) {

        $this->_getPlaylistTrack("b.unique_id = ? AND b.stream_id = ?", [$uniqueID, $this->key])
            ->then(function ($track) use ($offset, $notify) {
                $this->_setCurrentTrack($track, $offset, $notify);
            })->justThrow(ControllerException::noTrack($uniqueID));

        return $this;

    }

    public function scPlayByOrderID($order) {

        $this->_getPlaylistTrack("b.t_order = ? AND b.stream_id = ?", [$order, $this->key])
            ->then(function ($track) {
                $this->_setCurrentTrack($track, 0, true);
            })->otherwise(function () {
                $this->scPlay();
            });

        return $this;

    }

} 