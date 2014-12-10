<?php

/**
 * Additional methods for radioStream class
 *
 * @author Roman
 */
class radioStreamHelper
{
    private $object, $database, $vendee;
    private $tracksCount, $tracksDuration;
    
    private $required_keys = array(
        'sid', 'uid', 'name', 'permalink', 'info', 'status',
        'started', 'started_from', 'access', 'category', 'hashtags'
    );

    public function __construct(array $stream)
    {
        foreach($this->required_keys as $key)
        {
            if (array_key_exists($key, $stream) === false)
            {
                throw new streamException("Stream object could not be created. Insufficient keys.", 1001);
            }
        }

        $this->database = Database::getInstance();
        $this->vendee = Visitor::getInstance();
        $this->object = $stream;
        
        /* Stat values */
        $temp = $this->database->query_single_row("SELECT * FROM `r_static_stream_vars` WHERE `stream_id` = ?", array($this->object['sid']));
       
        $this->tracksCount = $temp['tracks_count'];
        $this->tracksDuration = $temp['tracks_duration'];
    }
    
    public function getTracksCount()
    {
        return (int) $this->tracksCount;
    }
    
    public function getTracksDuration()
    {
        return (int) $this->tracksDuration;
    }
    
    public function getStreamPosition($time = null)
    {
        $microTime = ($time ==! null) ? $time : System::time();
        $result = ($microTime - $this->object['started'] + $this->object['started_from']) % $this->tracksDuration;
        
        return $result;
    }

}
