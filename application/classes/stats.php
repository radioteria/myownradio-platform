<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stats
 *
 * @author Roman
 */
class stats
{
    static function getStatsGlobal($uid)
    {
        $user_streams = stream::getStreams($uid);
        $streams_array = array();
        foreach($user_streams as $stream)
        {
            $streams_array[] = $stream['sid'];
        }
        $stats_data = db::query("SELECT UNIX_TIMESTAMP(`date`) as `date_unix`, SUM(`listeners`) as `listeners`, AVG(`average_listening`) as `average_listening` FROM `r_listener_stats_daily` WHERE FIND_IN_SET(`stream_id`, ?) GROUP BY `date`", array(implode(",", $streams_array)));
        return $stats_data;
    }
}
