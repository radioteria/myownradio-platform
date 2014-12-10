<?php

class post_controller extends authController
{
    /* Methods for manipulations with TRACKS */
    public function upload()
    {
        if(isset($_FILES['file']))
        {
            echo track::uploadFile($_FILES['file'], application::post('stream_id', null, REQ_INT));
        }
    }
    
    public function changePassword()
    {
        $old = application::post("old",  null, REQ_STRING);
        $new = application::post("new1", null, REQ_STRING);

        if(!user::checkAccount(user::getCurrentUserName(), $old))
        {
            exit(misc::outputJSON("INCORRECT_PASSWORD"));
        }

        if($old === $new)
        {
            exit(misc::outputJSON("PASSWORDS_EQUALS"));
        }

        // Change password
        echo user::changePassword(user::getCurrentUserId(), user::getCurrentUserName(), $new);
    }
    
    public function changeTrackInfo()
    {
        echo track::updateFileInfo(
            application::post('track_id',   NULL, 'string'), 
            application::post('artist',     NULL, 'string'), 
            application::post('title',      NULL, 'string'), 
            application::post('album',     NULL, 'string'), 
            application::post('track_number',     NULL, 'string'), 
            application::post('genre',      NULL, 'string'),
            application::post('date',      NULL, 'string')
        );
    }
    
    public function removeTrack()
    {
        $track_id = application::post('track_id', "", REQ_STRING);

        try 
        {
            $queue = new trackqueue($track_id);
        }
        catch(Exception $ex)
        {
            die(misc::outputJSON("EXCEPTION", $ex));
        }

        echo track::deleteTrackFile($queue);
    }
    
    public function setTrackColor()
    {
        $track_id = application::post("track_id", null, REQ_STRING);
        $track_color = application::post("color", null, REQ_INT);

        if($track_id === null || $track_color === null)
        {
            exit(misc::outputJSON("BAD_REQUEST"));
        }

        $valid_colors = array(0, 1, 2, 3, 4);

        if(array_search($track_color, $valid_colors) === false)
        {
            exit(misc::outputJSON("BAD_REQUEST"));
        }
    }


    /* Methods for manipulations with STREAMS */
    public function addTrackToStream()
    {
        $track_id = application::post('track_id', "", REQ_STRING);
        $stream_id = application::post('stream_id', NULL, REQ_INT);

        try
        {
            $queue = new trackqueue($track_id);
        } 
        catch (Exception $ex) 
        {
            exit(misc::outputJSON("EXCEPTION"));
        }

        if (is_null($stream_id))
        {
            exit(misc::outputJSON("EXCEPTION"));
        }

        $sintance = application::singular('stream', $stream_id);

        if ($sintance->getOwner() != user::getCurrentUserId())
        {
            exit(misc::outputJSON("NO_PERMISSION"));
        }

        echo $sintance->addNewTracks($queue);
    }
    
    
    public function changeStreamInfo()
    {
        $strm_name   = application::post('stream_name', NULL, REQ_STRING);
        $strm_info   = application::post('stream_info', NULL, REQ_STRING);
        $strm_genres = application::post('stream_genres', NULL, REQ_STRING);
        $stream_id   = application::post('stream_id', NULL, REQ_INT);

        if(is_null($strm_name) || is_null($strm_info) || is_null($strm_genres) || is_null($stream_id))
        {
            exit("Some parametes missed.");
        }

        echo application::singular('stream', $stream_id)->changeInfo($strm_name, $strm_info, $strm_genres);
    }
    
    public function changeStreamState()
    {
        $new_state = application::post('new_state', NULL, REQ_INT);
        $new_offset = application::post('start_offset', 0, REQ_INT);
        $new_unique = application::post('new_unique', NULL, REQ_STRING);
        $stream_id = application::post('stream_id', NULL, REQ_INT);

        if (is_int($stream_id))
        {
            if (is_null($new_unique))
            {
                echo application::singular('stream', $stream_id)->setState($new_state, $new_offset);
            }
            else
            {
                echo application::singular('stream', $stream_id)->setCurrentTrack($new_unique);
            }
        }
    }
    
    public function createStream()
    {
        $strm_name   = application::post('stream_name', NULL, REQ_STRING);
        $strm_info   = application::post('stream_info', "", REQ_STRING);
        $strm_genres = application::post('stream_tags', "", REQ_STRING);
        $strm_permalink = application::post('stream_perm', "", REQ_STRING);
        $strm_category = application::post('stream_category', 13, REQ_INT);

        if(is_null($strm_name))
        {
            exit("Some parametes missed.");
        }

        echo stream::createStream($strm_name, $strm_info, $strm_genres, $strm_permalink, $strm_category);
    }
    
    public function purgeStream()
    {
        $stream_id = application::post('stream_id', NULL, REQ_INT);
        if(!is_null($stream_id))
        {
            echo application::singular('stream', $stream_id)->selfPurge();
        }
    }
    
    public function rearrangeStream()
    {
        $stream_id = application::post('stream_id', NULL, REQ_INT);
        $target    = application::post('target', NULL, REQ_STRING);
        $index    = application::post('index', NULL, REQ_INT);

        if (is_int($stream_id))
        {
            echo application::singular('stream', $stream_id)->streamReorder($target, $index);
        }
    }
    
    public function removeStream()
    {
        $stream_id = application::post('stream_id', NULL, REQ_INT);

        if(!is_null($stream_id))
        {
            echo application::singular('stream', $stream_id)->selfDelete();
        }
    }
    
    public function shuffleStream()
    {
        $stream_id = application::post('stream_id', NULL, REQ_INT);

        if (is_int($stream_id))
        {
            echo application::singular('stream', $stream_id)->streamShuffle();
        }
    }
    
    public function removeTrackFromStream()
    {
        $stream_id = application::post('stream_id', NULL, 'int');
        $unique_id = application::post('unique_id', "", 'string');

        if (is_null($stream_id))
        {
            exit( misc::outputJSON("NO_STREAM_ID") );
        }

        try 
        {
            $queue = new trackqueue($unique_id);
        }
        catch(Exception $ex)
        {
            die(misc::outputJSON("EXCEPTION", $ex));
        }

        echo application::singular('stream', $stream_id)->reloadTracks()->removeTrack($queue);
    }
    
    public function eventListen()
    {
        $startFrom = application::post("s", 0, REQ_INT);
        $waitTime  = config::getSetting("status", "event_interval", 15);
        $startTime = time();

        do 
        {
            $data = array();
            foreach(db::query("SELECT * FROM `m_events_log` WHERE `event_id` > ? AND `user_id` = ? ORDER BY `event_id` ASC", array($startFrom, user::getCurrentUserId())) as $row)
            {
                $data[] = $row;
                $startFrom = $row['event_id'];
            }
            usleep(250000);
        }
        while(time() - $startTime < $waitTime && count($data) == 0);

        echo misc::outputJSON("STATUS_OK", array(
            'LAST_EVENT_ID' => $startFrom,
            'EVENTS' => $data
        ));
    }
    

}

class get_controller extends authController
{
    public function previewAudio()
    {
        $track_id = application::get('track_id', NULL, REQ_INT);

        if(is_null($track_id))
        {
            exit(module::getModule("error.static.404"));
        }

        $track_instance = application::singular("track", $track_id);

        if($track_instance->getTrackOwner() != user::getCurrentUserId())
        {
            exit(module::getModule("error.permission"));
        }

        $file = null;

        if(file_exists($track_instance->originalFile()))
        {
            $file = $track_instance->originalFile();
        }
        else if(file_exists($track_instance->lowQualityFile()))
        {
            $file = $track_instance->lowQualityFile();
        }

        if($file === null)
        {
            exit(module::getModule("error.static.404"));
        }

        $cmd = sprintf(config::getSetting("streaming", "track_preview"), escapeshellarg($file));

        $fh = popen($cmd, "r");

        header("Content-Type: audio/mpeg");

        while($data = fread($fh, 4096))
        {
            echo $data;
            flush();
        }

        pclose($fh);


    }
}