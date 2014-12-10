<?php

class otrack extends track
{
    function getOffset()
    {
        return (int) $this->track_object['time_offset'];
    }
    
    function getUnique()
    {
        return $this->track_object['unique_id'];
    }
    
    function getTrackCursor()
    {
        return (int) $this->track_object['cursor'];
    }
    
    function getTrackOrder()
    {
        return (int) $this->track_object['t_order'];
    }
}
