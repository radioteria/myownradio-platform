<?php


class get_controller extends controller {

    public function streamStatus() {
        header("Content-Type: application/json");

        $stream = new radioStream(application::get("id", "", REQ_STRING));
        $debug = application::get("debug", 0, REQ_INT);
        $custom = application::get("time", System::time() - 5000, REQ_INT);

        $streamDetails = $stream->getDetails();

        $status = array(
            'stream_id' => $stream->getId(),
            'server_time' => System::time(),
            'stream_status' => $streamDetails->getState()
        );

        if ($streamDetails->getState() !== 0) {
            try {
                $streamHelper = $stream->getHelper();
                $streamTrackList = $stream->getTrackList();
                $streamPosition = $streamHelper->getStreamPosition($custom);
                $nowPlaying = $streamTrackList->getTrackAtTime($streamPosition);

                $trackPosition = $streamPosition - $nowPlaying->getTimeOffset();

                $status['now_playing'] = array(
                    'track_id' => $nowPlaying->getId(),
                    'title' => $nowPlaying->getCaption(),
                    'unique_id' => $nowPlaying->getUniqueId(),
                    'duration' => $nowPlaying->getDuration(),
                    'position' => $trackPosition,
                    'started_at' => System::time() - $trackPosition,
                    'time_left' => $nowPlaying->getDuration() - $trackPosition,
                    'percent' => floor(100 / $nowPlaying->getDuration() * $trackPosition),
                    'order_index' => $nowPlaying->getOrderIndex()
                );

                $prevOrderIndex = ($nowPlaying->getOrderIndex() > 1)
                    ? ($nowPlaying->getOrderIndex() - 1)
                    : $streamHelper->getTracksCount();

                $nextOrderIndex = ($nowPlaying->getOrderIndex() < $streamHelper->getTracksCount())
                    ? ($nowPlaying->getOrderIndex() + 1)
                    : 1;

                $prevPlaying = $streamTrackList->getTrackByOrderIndex($prevOrderIndex);

                $status['previous'] = array(
                    'track_id' => $prevPlaying->getId(),
                    'title' => $prevPlaying->getCaption(),
                    'unique_id' => $prevPlaying->getUniqueId(),
                    'duration' => $prevPlaying->getDuration(),
                    'order_index' => $prevPlaying->getOrderIndex()
                );

                $nextPlaying = $streamTrackList->getTrackByOrderIndex($nextOrderIndex);

                $status['next'] = array(
                    'track_id' => $nextPlaying->getId(),
                    'title' => $nextPlaying->getCaption(),
                    'unique_id' => $nextPlaying->getUniqueId(),
                    'duration' => $nextPlaying->getDuration(),
                    'order_index' => $nextPlaying->getOrderIndex()
                );
            } catch (streamException $ex) {
                $status['error'] = $ex->getMessage();
            }
        }

        if ($debug) {
            print_r($status);
        } else {
            echo misc::dataJSON($status);
        }
    }
}