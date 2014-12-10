<?php

class radioStreamTrackList extends Model
{
    private $stream_id, $vendee, $parent;

    public function __construct($stream_id, $parent = null)
    {
        parent::__construct();
        $this->vendee = Visitor::getInstance();
        $this->stream_id = $stream_id;
        $this->parent = $parent;
    }
    
    public function getTracks(long $from = null, long $limit = null)
    {
        $qb = new querybuilder("SELECT");
        $qb
                ->addSelect("a.*")
                ->addSelect("b.`unique_id`")
                ->addSelect("b.`t_order`")
                ->addSelect("b.`time_offset`")
                
                ->addFrom("`r_tracks` a")
                ->addFrom("`r_link` b")
                
                ->addWhere("a.`tid` = b.`track_id`")
                ->addWhere("b.`stream_id` = ?")
                ->addWhere("a.`lores` = 1")
                
                ->addOrder("b.`t_order`");
                
        if ($from !== null && $limit !== null)
        {
            $qb->setLimit($from, $limit);
        }
        return $this->database->query($qb->build(), array($this->stream_id));
    }
    
    public function getTrackAtTime($time)
    {
        $track_item = $this->database->query_single_row(
            "SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE b.`time_offset` <= :time AND b.`time_offset` + a.`duration` >= :time AND a.`tid` = b.`track_id` AND b.`stream_id` = :id AND a.`lores` = 1 ORDER BY b.`t_order`",
            array('time' => $time, 'id' => $this->stream_id));
            
        if($track_item !== null)
        {
            $track_item['cursor'] = $time - $track_item['time_offset'];
            return new radioStreamTrackItemInfo($track_item);
        }
        return null;
    }
    
    public function getTrackByOrderIndex($index)
    {
        $object = $this->database->query_single_row(
            "SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE b.`t_order` = ? AND a.`tid` = b.`track_id` AND b.`stream_id` = ?",
            array($index, $this->stream_id));
        
        if($object !== null)
        {
            return new radioStreamTrackItemInfo($object);
        }
        
        throw new streamException(sprintf("Could not find track by index '%d' in stream '%d'", $index, $this->stream_id));
    }

    public function getTrackByUniqueId(validUniqueId $unique_id)
    {
        $object = $this->database->query_single_row(
                "SELECT a.*, b.`unique_id`, b.`t_order`, b.`time_offset` FROM `r_tracks` a, `r_link` b WHERE b.`unique_id` = ? AND a.`tid` = b.`track_id` AND b.`stream_id` = ?",
                array($unique_id->get()));
        
        if ($object !== null)
        {
            return new radioStreamTrackItemInfo($object);
        }

        throw new streamException(sprintf("Could not find track '%s' in stream '%d'", $unique_id, $this->stream_id));
    }

    public function shuffle()
    {
        $this->quietAction(function(){
            $this->database->query_update("CALL P_SHUFFLE_STREAM(?)", array($this->stream_id));
            $this->optimizeTrackList();
        });
        return misc::okJSON(array(array("STREAM_RELOAD" => $this->stream_id)));
    }
    
    public function addTracks(validTrackList $tracks)
    {
        $items = $this->quietAction(function() use ($tracks) {
            $streamHelper       = $this->parent->getHelper();
            $nextTrackItem      = $streamHelper->getTracksCount();
            $lastTimeOffset     = $streamHelper->getTracksDuration();
            
            $success = 0;
            foreach($tracks->getArray() as $track)
            {
                try
                {
                    $trackInstance = new radioTrackItem($track);
                } catch (trackException $ex) {
                    continue;
                }
                
                if ($trackInstance->getDetails()->getOwner() !== $this->parent->getDetails()->getOwner())
                {
                    continue;
                }
                
                $success += $this->database->query_update("INSERT INTO `r_link` VALUES (NULL, ?, ?, ?, ?, ?)", array(
                    $this->stream_id, $track, ++$nextTrackItem, $this->newUniqueId(), $lastTimeOffset));
                
                $lastTimeOffset += $trackInstance->getDetails()->getDuration();
            }
            //$this->optimizeTrackList();
            return $success;
        });
        
        return misc::okJSON();
    }


    public function removeTracks(validUniqueList $tracks)
    {
        $this->quietAction(function() use ($tracks) {
            $result = $this->database->query_update("DELETE FROM `r_link` WHERE FIND_IN_SET(`unique_id`, ?)", array($tracks));
            $this->optimizeTrackList();
            return $result;
        });
        
        return misc::okJSON();
    }

    public function moveTrack(validUniqueId $uniqueId, $newIndex)
    {
        $result = $this->quietAction(function () use ($uniqueId, $newIndex) {
            return $this->database->query_single_col("SELECT NEW_STREAM_SORT(?, ?, ?)", array($this->stream_id, $uniqueId, $newIndex));
        });
        
        if ($result !== null)
        {
            return misc::okJSON();
        }
        else
        {
            throw new patNothingChangedException();
        }
    }

    private function quietAction($callback)
    {
        // Saving current stream state
        $streamHelper           = $this->parent->getHelper();
        $streamDetails          = $this->parent->getDetails();

        if ($streamDetails->getState() === 1)
        {

            $currentStreamPosition  = $streamHelper->getStreamPosition();
            $playingTrack           = $this->getTrackAtTime($currentStreamPosition);

            $currentTrackUniqueId = $playingTrack->getUniqueId();
            $currentTrackPosition = $currentStreamPosition - $playingTrack->getTimeOffset();
        }
        
        // Call user action
        $result = call_user_func($callback);
        
        // Restoring current stream state
        if ($streamDetails->getState() === 1)
        {
            $this->setCurrent($currentTrackUniqueId, $currentTrackPosition);
        }
        
        return $result;
    }
    
    public function setCurrent($unique_id, $position, $forced = false)
    {
        $trackOffset = $this->database->query_single_col("SELECT `time_offset` FROM `r_link` WHERE `unique_id` = ? AND `stream_id` = ?", 
                array($unique_id, $this->stream_id));
        
        if ($trackOffset === null)
        {
            // start next track
            $this->parent->reload();
            $this->parent->notifyStreamers();
            return;
        }
        
        $newStreamOffset = $trackOffset + $position;
        
        $this->database->query_update("UPDATE `r_streams` SET `started_from` = :from, `started` = :time, `status` = 1 WHERE `sid` = :id", 
                array(
                    'id'    => $this->stream_id,
                    'from'  => $newStreamOffset,
                    'time'  => System::time()));
        
        $this->parent->reload();
        
        if ($forced === true)
        {
            $this->parent->notifyStreamers();
            myRedis::set(sprintf("myownradio.biz:state_changed:stream_%d", $this->stream_id), System::time());
        }
        
        return $this;
    }


        public function optimizeTrackList()
    {
        $tracks = $this->database->query("SELECT a.`duration`, b.`id` as `base_id` FROM `r_tracks` a, `r_link` b WHERE a.`tid` = b.`track_id` AND b.`stream_id` = ? ORDER BY b.`t_order` ASC", array($this->stream_id));
        $time_offset = 0;
        $order_index = 1;
        foreach ($tracks as $track)
        {
            $this->database->query_update("UPDATE `r_link` SET `time_offset` = ?, `t_order` = ? WHERE `id` = ?", 
                    array($time_offset, $order_index++, $track['base_id']));
            
            $time_offset += $track['duration'];
        }
        return $this;
    }

    private function newUniqueId()
    {
        do
        {
            $generated = misc::generateId();
        }
        while ($this->database->query_single_col("SELECT COUNT(*) FROM `r_link` WHERE `unique_id` = ?", array($generated)) > 0);
        return $generated;
    }

}
