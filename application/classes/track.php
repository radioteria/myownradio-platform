<?php

class track
{

    protected $track_object = NULL;

    function __construct($track_id)
    {
        if(is_int($track_id) || is_string($track_id))
        {
            $this->track_object = db::query_single_row("SELECT * FROM `r_tracks` WHERE `tid` = ?", array($track_id));
            if(is_null($this->track_object))
            {
                throw new Exception("Track not exists");
            }
        }
        elseif(is_array($track_id))
        {
            $this->track_object = $track_id;
        }
        else
        {
            throw new Exception("Incorrect input arguments");
        }
    }
    
    function __destruct()
    {
        $this->track_object = NULL;
    }

    function originalFile()
    {
        return sprintf("%s/ui_%d/a_%03d_original.%s", 
            config::getSetting("content", "content_folder"), 
            $this->track_object['uid'], 
            $this->track_object['tid'], 
            $this->track_object['ext']
        );
    }

    function hasLowResolution()
    {
        return $this->track_object['lores'] == '1';
    }
    
    function lowQualityFile()
    {
        return sprintf("%s/ui_%d/lores_%03d.mp3", 
            config::getSetting("content", "content_folder"), 
            $this->track_object['uid'], 
            $this->track_object['tid']
        );
    }

    function makeArray()
    {
        return $this->track_object;
    }
    
    function Exists()
    {
        return is_array($this->track_object);
    }

    function getTrackOwner()
    {
        return $this->track_object['uid'];
    }
    
    function getTrackCaption()
    {
        return sprintf("%s - %s", $this->track_object['artist'], $this->track_object['title']);
    }

    function getTrackId()
    {
        return (int) $this->track_object['tid'];
    }
    
    function getTrackTitle()
    {
        return $this->track_object['title'];
    }
    
    function getTrackArtist()
    {
        return $this->track_object['artist'];
    }
    
    function getTrackDuration()
    {
        return (int) $this->track_object['duration'];
    }

    
    function getTrackStreams()
    {
        return db::query("SELECT `stream_id`, GROUP_CONCAT(`unique_id`) as `unique_ids` FROM `r_link` WHERE `track_id` = ? GROUP BY `stream_id`", array($this->getTrackId()));
    }
    
    function selfDelete()
    {
        db::query_update("DELETE FROM `r_tracks` WHERE `tid` = ?", array($this->track_object['tid']));
        $this->track_object = NULL;
    }

    function removeFromStreams()
    {
        foreach($this->getTrackStreams() as $row)
        {
            try
            {
                $stream_instance = new stream($row['stream_id']);
                $stream_instance->reloadTracks()->removeTrack($row['unique_ids']);
                unset($stream_instance);
            }
            catch(Exception $e)
            {
                misc::writeDebug(sprintf("Warning: stream not exists: %d", $row['stream_id']));
            }
        } 
        return $this;
    }
    
    function removeFromFS()
    {
        $or_file = $this->originalFile();
        $lq_file = $this->lowQualityFile();

        $or_st = file_exists($or_file) ? unlink($or_file) : 1;
        $lq_st = file_exists($lq_file) ? unlink($lq_file) : 1;

        if($or_st && $lq_st)
        {
            return true;
        }
        
        return false;
    }
    
    /* 
     * Static methods section
     */
    static function updateFileInfo($track_id, $artist = "", $title = "", $album = "", $track_number = "", $genre = "", $date = "")
    {
       
        $queue = new trackqueue($track_id);
        
        $queue->iterator(function($track_id) use ($artist, $title, $album, $track_number, $genre, $date) {
            
            try 
            {
                $track = new track($track_id);
            } 
            catch(Exception $ex) 
            {
                return "TRACK_NOT_EXISTS";
            }
        
            if($track->getTrackOwner() != user::getCurrentUserId())
            {
                return "NO_PERMISSION";
            }
            
            unset($track);
            
            $builder = new querybuilder("UPDATE");
            $builder->setUpdate("`r_tracks`")
                    ->addWhere(sprintf("`tid` = %d", $track_id));
                    
            if($artist !== config::getSetting("tagger", "do_not_change_string"))
            {
                $builder->addSet(sprintf("`artist` = %s", db::quote($artist)));
            }

            if($title !== config::getSetting("tagger", "do_not_change_string"))
            {
                $builder->addSet(sprintf("`title` = %s", db::quote($title)));
            }

            if($album !== config::getSetting("tagger", "do_not_change_string"))
            {
                $builder->addSet(sprintf("`album` = %s", db::quote($album)));
            }

            if($track_number !== config::getSetting("tagger", "do_not_change_string"))
            {
                $builder->addSet(sprintf("`track_number` = %s", db::quote($track_number)));
            }

            if($genre !== config::getSetting("tagger", "do_not_change_string"))
            {
                $builder->addSet(sprintf("`genre` = %s", db::quote($genre)));
            }

            if($date !== config::getSetting("tagger", "do_not_change_string"))
            {
                $builder->addSet(sprintf("`date` = %s", db::quote($date)));
            }
        
            db::query_update($builder);
            
            unset($builder);
            
            return array("SUCCESS", (new track($track_id))->makeArray());
            
        });
        
        return misc::outputJSON("SUCCESS", $queue->toArray());
    
    }
    
    static function deleteTrackFile($queue)
    {
        
        stream::removeTracksFromAllStreams($queue);
        
        $queue->iterator(function($el) {
            // create termorary track object
            try
            {
                $trackObject = new track($el);
            }
            catch(Exception $e)
            {
                return 'TRACK_NOT_EXISTS';
            }
            
            // checking for our permission
            if($trackObject->getTrackOwner() != user::getCurrentUserId())
            {
                return 'NO_PERMISSION';
            }
            
            // deleting files physically
            if($trackObject->removeFromFS() === false)
            {
                return 'IO_ACCESS_ERROR';
            }
            
            // deleting track from database
            $trackObject->selfDelete();
            
            // destroying track object
            unset($trackObject);

            // C.O.: returning 'success'
            return 'SUCCESS';
            
        });
        
        return misc::outputJSON("SUCCESS", $queue->toArray());

    }

    static function uploadFile($file, $stream_id = null)
    {
        if( !isset($file) || $file['error'] != 0)
        {
            return misc::outputJSON("UPLOAD_ERROR_NO_FILE") ;
        }

        if(array_search($file['type'], config::getSetting('upload', 'supported_audio')) == -1)
        {
            return misc::outputJSON("UPLOAD_ERROR_UNSUPPORTED");
        }

        $echoprint = ""; //misc::get_audio_echoprint($file['tmp_name']);
        
        if($echoprint === false)
        {
            return misc::outputJSON("UPLOAD_ERROR_CORRUPTED_AUDIO");
        }
        
        $audio_tags = misc::get_audio_tags($file['tmp_name']);

        if(empty($audio_tags['DURATION']) || $audio_tags['DURATION'] == 0)
        {
            return misc::outputJSON("UPLOAD_ERROR_CORRUPTED_AUDIO");
        }

        if($audio_tags['DURATION'] > config::getSetting('upload', 'maximal_length'))
        {
            return misc::outputJSON("UPLOAD_ERROR_LONG_AUDIO");
        }
        
        if($audio_tags['DURATION'] > user::userUploadLeft() && user::userUploadLimit() > 0)
        {
            return misc::outputJSON("UPLOAD_ERROR_NO_SPACE");
        }
        
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        
        db::query_update("INSERT INTO `r_tracks` SET "
                . "`uid` = ?, "
                . "`filename` = ?, "
                . "`ext` = ?, "
                . "`track_number` = ?, "
                . "`artist` = ?, "
                . "`title` = ?, "
                . "`album` = ?, "
                . "`genre` = ?, "
                . "`date` = ?, "
                . "`duration` = ?, "
                . "`filesize` = ?, "
                . "`uploaded` = ?", 
            array(
                user::getCurrentUserId(),
                $file['name'],
                $extension,
                empty($audio_tags["TRACKNUMBER"])   ? ""                    : $audio_tags["TRACKNUMBER"],
                empty($audio_tags["PERFORMER"])     ? ""                    : $audio_tags["PERFORMER"],
                empty($audio_tags["TITLE"])         ? $file['name']         : $audio_tags["TITLE"],
                empty($audio_tags["ALBUM"])         ? ""                    : $audio_tags["ALBUM"],
                empty($audio_tags["GENRE"])         ? ""                    : $audio_tags["GENRE"],
                empty($audio_tags["RECORDED_DATE"]) ? ""                    : $audio_tags["RECORDED_DATE"],
                $audio_tags["DURATION"],
                filesize($file['tmp_name']),
                time()
            )
        );

        $last_id = db::lastInsertId();

        if(!$last_id)
        {
            return misc::outputJSON("UPLOAD_WAS_NOT_ADDED", ['sql' => db::lastError()]);
        }
        else
        {
            db::query_update("INSERT INTO r_echoprints SET  tid = ?, echoprint = ?", array($last_id, $echoprint));
            if(!is_null($stream_id))
            {
                $stream = new stream($stream_id);
                $stream->addNewTrack($last_id);
            }
        }

        $temp_track = new track($last_id);
        
        if(move_uploaded_file($file['tmp_name'], $temp_track->originalFile()))
        {
            misc::writeDebug(sprintf("User #%s successfully uploaded track \"%s\"", user::getCurrentUserId(), $file['name']));
            return misc::outputJSON("UPLOAD_SUCCESS", $temp_track->makeArray());
        }
        else
        {
            return misc::outputJSON("UPLOAD_ERROR_DISK_ACCESS_ERROR");
        }
        
    }
    
    static function getTracks($user_id, $from = 0, $limit = 5000)
    {
        $track_data = db::query("SELECT * FROM `r_tracks` WHERE `uid` = ? ORDER BY `uploaded` DESC LIMIT $from, $limit", array($user_id));
        return $track_data;
    }

    static function getUnusedTracks($user_id, $from = 0, $limit = 5000)
    {
        $track_data = db::query("SELECT * FROM `r_tracks` WHERE `uid` = ? AND `tid` NOT IN (SELECT `track_id` FROM `r_link` WHERE 1) ORDER BY `uploaded` DESC LIMIT $from, $limit", array($user_id));
        return $track_data;
    }

    static function getFilteredUnusedTracks($user_id, $match = "*", $from = 0, $limit = 5000)
    {
        $track_data = db::query("SELECT * FROM `r_tracks` WHERE `uid` = ? AND `tid` NOT IN (SELECT `track_id` FROM `r_link` WHERE 1) AND MATCH(`artist`, `album`, `title`, `genre`) AGAINST (? IN BOOLEAN MODE) ORDER BY `uploaded` DESC LIMIT $from, $limit", array($user_id, $match));
        return $track_data;
    }
    
    static function getFilteredTracks($user_id, $match = "*", $from = 0, $limit = 5000)
    {
        $track_data = db::query("SELECT * FROM `r_tracks` WHERE `uid` = ? AND MATCH(`artist`, `album`, `title`, `genre`) AGAINST (? IN BOOLEAN MODE) ORDER BY `uploaded` DESC LIMIT $from, $limit", array($user_id, $match));
        return $track_data;
    }

    static function getTracksCount($user_id)
    {
        return (int) db::query_single_col("SELECT `tracks_count` FROM `r_static_user_vars` WHERE `user_id` = ?", array($user_id));
    }

    static function getTracksDuration($user_id)
    {
        return (int) db::query_single_col("SELECT `tracks_duration` FROM `r_static_user_vars` WHERE `user_id` = ?", array($user_id));
    }

}
