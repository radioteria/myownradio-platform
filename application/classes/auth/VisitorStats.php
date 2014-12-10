<?php

class VisitorStats extends Model
{
    use Singleton;
    
    private $streams_count = 0;
    private $tracks_count = 0;
    private $tracks_duration = 0;
    private $tracks_size = 0;

    public function __construct($user_id)
    {
        parent::__construct();

        $this->streams_count = $this->database->query_single_col("SELECT COUNT(*) FROM `r_streams` WHERE `uid` = ?", array($user_id));
        $this->tracks_count = $this->database->query_single_col("SELECT IFNULL(`tracks_count`, 0) FROM `r_static_user_vars` WHERE `user_id` = ?", array($user_id));
        $this->tracks_duration = $this->database->query_single_col("SELECT IFNULL(`tracks_duration`, 0) FROM `r_static_user_vars` WHERE `user_id` = ?", array($user_id));
        $this->tracks_size = $this->database->query_single_col("SELECT IFNULL(`tracks_size`, 0) FROM `r_static_user_vars` WHERE `user_id` = ?", array($user_id));
    }
    
    public function getStatus()
    {
        return array(            
            'user_streams_count' => (int) $this->streams_count,
            'user_tracks_count' => (int) $this->tracks_count,
            'user_tracks_time' => (int) $this->tracks_duration,
            'user_tracks_size' => (int) $this->tracks_size,
            'max_file_size' => disk_free_space(config::getSetting("content", "content_folder"))
        );
    }
}
