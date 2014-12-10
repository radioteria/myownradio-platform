<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of events
 *
 * @author Roman
 */
class events
{
    static function getLastID()
    {
        return (int) db::query_single_col("SELECT `event_id` FROM `m_events_log` WHERE `user_id` = ? ORDER BY `event_id` DESC LIMIT 1", array(user::getCurrentUserId()));
    }
}
