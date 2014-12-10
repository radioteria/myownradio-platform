<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of radioStreamTrackItemInfo
 *
 * @author Roman
 */
class radioStreamTrackItemInfo extends radioTrackItemInfo
{
    public function getUniqueId()
    {
        return $this->object['unique_id'];
    }
    
    public function getTimeOffset()
    {
        return (int) $this->object['time_offset'];
    }
    
    public function getOrderIndex()
    {
        return (int) $this->object['t_order'];
    }
}
