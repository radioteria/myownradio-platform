<?php

class stream extends streamreader 
{
    // @throws Exception if stream not found
    private $object = null,
            $stream_stats = null,
            $tracklist = null;

    // Stream object constructor
    function __construct($stream_id)
    {
        $this->object = db::query_single_row("SELECT * FROM `r_streams` WHERE `sid` = :sid OR `permalink` = :sid", array('sid' => $stream_id));
        if($this->object)
        {
            $this->object['tracks'] = $this->getTracksCount();
        }
        else
        {
            throw new Exception("Stream not exists!");
        }
    }
    
    function __destruct()
    {
        unset($this->object);
        //unset($this->stream_tracks);
        unset($this->stream_stats);
        unset($this->tracklist);
    }
    
    private function getStreamTracks(long $from = null, long $limit = null)
    {
        
        if (is_null($this->tracklist))
        {
            $this->reloadTracks();
        }

        if($from === null && $limit === null)
        {
            return $this->tracklist->toArray();
        }
        else
        {
            return array_slice($this->tracklist->toArray(), $from, $limit);
        }
    }
    
    function dbGetStreamTracks($from = 0, $limit = 1000)
    {
        return db::query("SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE a.`tid` = b.`track_id` AND b.`stream_id` = ? AND a.`lores` = 1 ORDER BY b.`t_order` LIMIT $from, $limit", array($this->getStreamId()));
    }
	
    function reloadTracks()
    {
        $tracks = db::query("SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE a.`tid` = b.`track_id` AND b.`stream_id` = ? AND a.`lores` = 1 ORDER BY b.`t_order`", array($this->object['sid']));
	$this->tracklist = new loop();
        foreach ($tracks as $track)
        {
            $this->tracklist->add($track);
        }
        return $this;
    }

    function optimizeTracksOffset()
    {
        $tracks = db::query("SELECT a.*, b.`unique_id`, b.`t_order`, b.`id` as `base_id` FROM `r_tracks` a, `r_link` b WHERE a.`tid` = b.`track_id` AND b.`stream_id` = ? AND a.`lores` = 1 ORDER BY b.`t_order`", array($this->getStreamId()));
        $time_offset = 0;
        foreach ($tracks as $track)
        {
            db::query_update("UPDATE `r_link` SET `time_offset` = ? WHERE `id` = ?", array($time_offset, $track['base_id']));
            $time_offset += $track['duration'];
        }
        return $this;
    }
	
    function getTrackAfter($trackObject)
    {
	// Check playlist length
        if($this->tracklist->length() === 0)
        {
            return null;
        }
		
	// Find track in trloop object
	$trackAfter = $this->tracklist->findKey("unique_id", $trackObject->getUnique());
	
	// Check track presence
	if($trackAfter === null)
	{
            return null;
	}
	
        return new otrack($trackAfter->next()->get());
    }
    
    function getTrackBefore($trackObject)
    {
	// Check playlist length
        if($this->tracklist->length() === 0)
        {
            return null;
        }
		
	// Find track in trloop object
	$trackAfter = $this->tracklist->findKey("unique_id", $trackObject->getUnique());
	
	// Check track presence
	if($trackAfter === null)
	{
            return null;
	}
	
        return new otrack($trackAfter->prev()->get());
    }
 
    private function getStreamStats()
    {
        if (is_null($this->stream_stats))
        {
            $this->reloadStats();
        }
        return $this->stream_stats;
    }


    function reloadStats()
    {
        $this->stream_stats = db::query_single_row("SELECT * FROM `r_static_stream_vars` WHERE `stream_id` = ?", array($this->getStreamId()));
        return $this;
    }

    function Exists()
    {
        return is_array($this->object);
    }

    function getDuration()
    {
        return $this->getStreamStats()['tracks_duration'];
    }

    function getRealDuration()
    {
        $duration = 0;
        $this->tracklist->each(function($track) use ($duration) {
            $duration += (int) $track['duration'];
        });
        return $duration;
    }
    
    function getStreamLink()
    {
        return "/stream/" . (strlen($this->getPermalink()) > 0 ? $this->getPermalink() : $this->getStreamId());
    }
    
    static function staticStreamLink($stream)
    {
        return "/stream/" . (strlen($stream['permalink']) > 0 ? $stream['permalink'] : $stream['sid']);
    }
    
    function getTracks($from = NULL, $limit = NULL)
    {
        return $this->getStreamTracks($from, $limit);
    }

    function getOwner()             {       return (int) $this->object['uid'];               }
    function getPermalink()         {       return $this->object['permalink'];               }
    function getTracksCount()       {       return (int) $this->getStreamStats()['tracks_count'];   }
    function getStreamId()          {       return (int) $this->object['sid'];               }
    function getState()             {       return (int) $this->object['status'];            }
    function getStreamName()        {       return $this->object['name'];                    }
    function getStreamGenres()      {       return $this->object['genres'];                  } 
    function getStreamInfo()        {       return $this->object['info'];                    }

    // Slow speed section
    function currentPlayingTime($realtime = false, $sync = false)
    {
        if ($this->getState() == 0 || $this->getDuration() == 0)
        {
            return null;
        }

        $stream = $this->object;
        return (($sync ? (int) $sync : application::getMicroTime($realtime)) - $stream['started'] + $stream['started_from']) % $this->getDuration();
    }

    function currentPlayingTrack($realtime = false, $sync = false)
    {
        if ($this->getState() == 0)
        {
            return null;
        }
        
        $current_time = $this->currentPlayingTime($realtime, $sync);

        $looking = $this->dbCurrentTrack($current_time);

        return $looking;
    }
    
    function dbCurrentTrack($playlistTime)
    {
        $track = db::query_single_row(
                "SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE b.`time_offset` <= :time AND b.`time_offset` + a.`duration` >= :time AND a.`tid` = b.`track_id` AND b.`stream_id` = :id AND a.`lores` = 1 ORDER BY b.`t_order`",
                array('time' => $playlistTime, 'id' => $this->getStreamId()));
        
        if($track)
        {
            $track['cursor'] = $playlistTime - $track['time_offset'];
            return new otrack($track);
        }
        else
        {
            return null;
        }
    }
    
    function dbNextTrack($trackObject)
    {
        $trackOrder = $trackObject->getTrackOrder();
        $tracksCount = $this->getTracksCount();
        $trackNext = null;
        
        if($trackOrder === $this->getTracksCount())
        {
            $trackNext = 1;
        }
        else
        {
            $trackNext = $trackOrder + 1;
        }

        $track = db::query_single_row(
                "SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE b.`t_order` = :order AND a.`tid` = b.`track_id` AND b.`stream_id` = :id ORDER BY b.`t_order`",
                array('order' => $trackNext, 'id' => $this->getStreamId()));
        
        if($track)
        {
            return new otrack($track);
        }
        else
        {
            return null;
        }
    }

    function setCurrentTrack($unique_id)
    {

        if ($this->object['uid'] != user::getCurrentUserId())
        {
            return misc::outputJSON("NO_PERMISSION");
        }

        foreach ($this->getStreamTracks() as $track)
        {
            if ($unique_id == $track['unique_id'])
            {
                $new_offset = $track['time_offset'];
            }
        }

        if (!isset($new_offset))
        {
            return misc::outputJSON("NO_TRACK");
        }

        $change_state_time = application::getMicroTime();

        db::query_update("UPDATE `r_streams` SET `status` = 1, `started` = ?, `started_from` = ? WHERE `sid` = ?", array($change_state_time, $new_offset, $this->getStreamId()));
        db::query_update("INSERT INTO `m_events_log` SET `user_id` = ?, `event_type` = 'STREAM_SET_CURRENT', `event_target` = ?, `event_value` = ?", array(
            user::getCurrentUserId(), $this->getStreamId(), $unique_id
        ));
        
        $this->object['started'] = $change_state_time;
        $this->object['started_from'] = $new_offset;
        
        myRedis::set(sprintf("myownradio.biz:state_changed:stream_%d", $this->getStreamId()), $change_state_time);

        return misc::outputJSON("SUCCESS");
    }

    function setState($state, $offset = 0, $restart = true)
    {
        if ( ! $this->Exists())
        {
            return misc::outputJSON("NO_STREAM");
        }

        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("NO_PERMISSION");
        }

        $change_state_time = application::getMicroTime();

        if ($state == 1)
        {
            $responce = db::query_update("UPDATE `r_streams` SET `status` = 1, `started` = ?, `started_from` = ? WHERE `sid` = ?", array($change_state_time, $offset, $this->getStreamId()));
            $this->object['status'] = 1;
            $this->object['started'] = $change_state_time;
            $this->object['started_from'] = $offset;
        }
        else if($state == -1)
        {
            $responce = db::query_update("UPDATE `r_streams` SET `status` = 1 - `status`, `started` = 0, `started_from` = 0 WHERE `sid` = ?", array($this->getStreamId()));
            $this->object['status'] = 1 - $this->object['status'];
            $this->object['started'] = 0;
            $this->object['started_from'] = 0;
        }
        else
        {
            $responce = db::query_update("UPDATE `r_streams` SET `status` = 0, `started` = 0, `started_from` = 0 WHERE `sid` = ?", array($this->getStreamId()));
            $this->object['status'] = 0;
            $this->object['started'] = 0;
            $this->object['started_from'] = 0;
        }

        if ($restart == true)
        {
            myRedis::set(sprintf("myownradio.biz:state_changed:stream_%d", $this->getStreamId()), $change_state_time);
        }
        
        return misc::outputJSON("SUCCESS", $this->makeArray());
    }

    function modifyCurrentTrackOffset($now_playing = null)
    {
        if ($this->getState() == 0)
        {
            return false;
        }

        $current_track = $now_playing ? $now_playing : $this->currentPlayingTrack();
        $track_found = false;

        if (!is_null($current_track))
        {
            misc::writeDebug("Now playing: " . $current_track->getTrackCaption() . " " . $current_track->getUnique());
            $prevUnique = $current_track->getUnique();
            $prevCursor = $current_track->getTrackCursor();

            foreach ($this->reloadTracks()->getStreamTracks() as $track)
            {
                if ($track['unique_id'] == $prevUnique)
                {
                    misc::writeDebug("Moved to: " . $track['unique_id']);
                    $this->setState(1, $track['time_offset'] + $prevCursor, false);
                    $track_found = true;
                    break;
                }
            }
        }
        else
        {
            $this->reloadTracks();
        }
        
        if ($track_found == false)
        {
            foreach ($this->getStreamTracks() as $track)
            {
                if ($track['t_order'] == $current_track->getTrackOrder())
                {
                    $this->setCurrentTrack($track['unique_id']);
                    return true;
                }
            }
            // Means that current playing track is removed
            $this->setState(1, 0, true);
        }
    }

    function selfPurge() 
    {
      
        // Check stream permission
        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("NO_PERMISSION");
        }
        
        db::query_update("DELETE FROM `r_link` WHERE `stream_id` = ?", array($this->getStreamId()));
        
        $this->tracklist->purge();
        //$this->stream_tracks = array();
        $this->streamOptimize()->setState(0)->modifyCurrentTrackOffset();
        
        return misc::outputJSON("SUCCESS");
    }
    
    function removeTrack($queue)
    {
        // Check stream permission
        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("NO_PERMISSION");
        }

        $now_playing = $this->currentPlayingTrack();
        
        misc::writeDebug(sprintf("Removing from stream %d tracks %s", $this->getStreamId(), $queue));
        
        $responce = db::query_update("DELETE FROM `r_link` WHERE FIND_IN_SET(`unique_id`, ?) AND `stream_id` = ?", array($queue, $this->object['sid']));
        if ($responce > 0)
        {
            $this->optimizeTracksOffset();
            $this->streamOptimize();
            $this->modifyCurrentTrackOffset($now_playing);
            return misc::outputJSON("SUCCESS", $queue->toArray());
        }
        else
        {
            return misc::outputJSON("ERROR");
        }
    }

    function addNewTracks($queue)
    {

        $this->getStreamTracks();
        $lastTrackId = $this->getTracksCount();

        $queue->iterator(function($el) use (&$lastTrackId) {
            try 
            {
                $track_test = new track($el);
            }
            catch (Exception $ex)
            {
                return 'TRACK_NOT_EXISTS';
            }
            
            // Check track permission
            if ($track_test->getTrackOwner() != user::getCurrentUserId())
            {
                return 'NO_PERMISSION';
            }  
            
            unset($track_test);
            
            $res = db::query_update("INSERT INTO `r_link` VALUES (NULL, ?, ?, ?, ?, 0)", array(
                $this->getStreamId(),
                $el,
                $lastTrackId ++,
                $this->genUniqueId()
            ));
            
            return 'SUCCESS';
            
        });
        
        $this->optimizeTracksOffset();
        $this->modifyCurrentTrackOffset();
        return misc::outputJSON("SUCCESS", $queue->toArray());
    }
    
    function streamShuffle()
    {
        // Check stream existence
        if ( ! $this->Exists())
        {
            return misc::outputJSON("ERROR_STREAM_NOT_EXISTS");
        }        
        // Check stream permission
        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("ERROR_NO_PERMISSION");
        }
        
        $this->reloadTracks();
        
        db::query_update("CALL P_SHUFFLE_STREAM(?)", array($this->getStreamId()));
        
        $this->optimizeTracksOffset();
        $this->modifyCurrentTrackOffset();
        
        return misc::outputJSON("SHUFFLE_SUCCESS");
        
       }

    function streamReorder($target, $index)
    {
        // Check stream existence
        if ( ! $this->Exists())
        {
            return misc::outputJSON("REARRANGE_STREAM_ERROR_NO_STREAM");
        }

        // Check stream permission
        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("REARRANGE_STREAM_ERROR_NOT_STREAM_OWNER");
        }
        
        $this->reloadTracks();

        $responce = db::query_update("CALL NEW_STREAM_SORT(?, ?, ?)", array($this->getStreamId(), $target, $index));
        
        if ($responce > 0)
        {
            db::query_update("INSERT INTO `m_events_log` SET `user_id` = ?, `event_type` = 'STREAM_SORT', `event_target` = ?, `event_value` = ?", array(
                user::getCurrentUserId(), $target, $index
            ));
            $this->streamOptimize();
            $this->optimizeTracksOffset();
            $this->modifyCurrentTrackOffset();
            return misc::outputJSON("REARRANGE_STREAM_SUCCESS");
        }
        else
        {
            return misc::outputJSON("REARRANGE_NOT_CHANGED");
        }
    }

    function changeInfo($name, $info, $genres)
    {
        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("NO_PERMISSION");
        }

        $this->prChangeStreamInfo($name, $info, $genres);
        
        return misc::outputJSON("CHANGE_INFO_SUCCESS", $this->makeArray());
    }
    
    private function prChangeStreamInfo($name, $info, $genres)
    {
        $builder = new querybuilder("UPDATE");
        $builder->setUpdate("`r_streams`")
                ->addSet("`name` = :name")
                ->addSet("`info` = :info")
                ->addSet("`genres` = :genres")
                ->addWhere("`sid` = :sid");
        
        db::query_update(
                $builder->build(), 
                array(
                    'name' => $name, 
                    'info' => $info, 
                    'genres' => $genres, 
                    'sid' => $this->getStreamId()
                ));
        
        $this->object['name'] = $name;
        $this->object['info'] = $info;
        $this->object['genres']  = $genres;
        return $this;
    }

    function selfDelete()
    {

        if ($this->getOwner() != user::getCurrentUserId())
        {
            return misc::outputJSON("NO_PERMISSION");
        }

        $builder = new querybuilder("DELETE");
        $builder->setFrom("`r_streams`")
                ->addWhere("`sid` = :sid");
        
        db::query_update($builder, array('sid' => $this->getStreamId()));

        return misc::outputJSON("SUCCESS");
        
    }

    function listenersCount()
    {
        return (int) db::query_single_col("SELECT `listeners_count` FROM `r_static_listeners_count` WHERE `stream_id` = ?", array($this->getStreamId()));
    }

    function makeArray()
    {
        return $this->object;
    }
    

    static function getStreamCoverLink($stream_data)
    {
        //$profileFile
    }
    
    private function streamOptimize()
    {
        db::query_update("call p_optimize_stream(?)", array($this->getStreamId()));
        return $this;
    }

    private function genUniqueId()
    {
        do
        {
            $generated = misc::generateId();
        }
        while (db::query_single_col("SELECT COUNT(*) FROM `r_link` WHERE `unique_id` = ?", array($generated)) > 0);
        return $generated;
    }
    
    static function createStream($name, $info = "", $genres = "", $permalink = "", $category = 13)
    {
        if (user::getCurrentUserId() == 0)
        {
            return misc::outputJSON('UNAUTHORIZED');
        }

        if(db::query_single_col("SELECT COUNT(*) FROM `r_streams` WHERE `permalink` = ?", array($permalink)) > 0 && $permalink != "")
        {
            return misc::outputJSON('PERMALINK_USED');
        }

        if((int) db::query_single_col("SELECT COUNT(*) FROM `r_categories` WHERE `id` = ?", array($category)) === 0)
        {
            return misc::outputJSON('NO_CATEGORY');
        }
        
        if($permalink === "")
        {
            $permalink = self::generateUniquePermalink($name);
        }
        
        
        $query = "INSERT INTO `r_streams` (`uid`, `name`, `info`, `genres`, `permalink`, `category`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = db::query($query, array(user::getCurrentUserId(), $name, $info, $genres, $permalink, $category));
        if ($result > 0)
        {
            return misc::outputJSON('SUCCESS');
        }
        else
        {
            return misc::outputJSON('ERROR');
        }
    }
    
    function hasCover()
    {
        return $this->object['hascover'];
    }

    // Stream status methods
    function getStreamStatus($sync = false)
    {
        $currentTrack = $this->getState() ? $this->currentPlayingTrack(true, $sync) : null;
        
        $item = array(
            'stream_id'     => $this->getStreamId(),
            'stream_status' => $this->getState(),
            'server_time'   => microtime(true) * 1000
        );
        
        if($currentTrack)
        {
            $nextTrack = $this->dbNextTrack($currentTrack);
            //$prevTrack = $this->getTrackBefore($currentTrack);
            $item = array_merge($item, array(
                'unique_id'     => $currentTrack->getUnique(),
                'track_id'      => $currentTrack->getTrackId(),
                'now_playing'   => $currentTrack->getTrackCaption(),
                'next_track'    => $nextTrack ? $nextTrack->getTrackCaption() : '',
                //'prev_track'    => $prevTrack->getTrackCaption(),
                'duration'      => $currentTrack->getTrackDuration(),
                'position'      => $currentTrack->getTrackCursor(),
                'started_at'    => application::getMicroTime(false) - $currentTrack->getTrackCursor(),
                'time_left'     => $currentTrack->getTrackDuration() - $currentTrack->getTrackCursor(),
                't_order'       => $currentTrack->getTrackOrder()
            ));
        }
        
        return $item;
    }
    
    static function removeTracksFromAllStreams($track_ids)
    {
        
        $stream_ids = db::query("SELECT `stream_id` FROM `r_link` WHERE FIND_IN_SET(`track_id`, ?) GROUP BY `stream_id`", array($track_ids));
        foreach($stream_ids as $stream_id)
        {
            $stream = new stream($stream_id['stream_id']);
            
            if($stream->getOwner() !== user::getCurrentUserId()) 
                continue;
            
            $stream->reloadTracks()->removeTrack($track_ids);
            unset($stream);
        }
    }
}
