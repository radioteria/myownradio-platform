<?php

/*
 * This class supports icy-metadata in
 * radio streams
 */

class flow
{
    private static 
            $meta_interval = 8192,
            $meta_enabled = false,
            $meta_buffer = "",
            $meta_title = "No metadata",
            $meta_new = true,
            $global_counter = 0;
    
    static function setActive() {
        self::$meta_enabled = true;
    }
    
    static function setInterval($interval) 
    {
        self::$meta_interval = $interval;
    }
    
    static function setTitle($title)
    {
        self::$meta_title = $title;
        self::$meta_new = true;
    }
    
    static private function writeTag()
    {
        if(self::$meta_new == true)
        {
            $meta_pack = "";
            $title = sprintf("StreamTitle='%s';", self::$meta_title);
            $title_length = ceil(strlen($title) / 16);
            $meta_pack .= pack("C", $title_length);
            $meta_pack .= str_pad($title, $title_length * 16);
            self::$meta_new = false;
            return $meta_pack;
        }
        else
        {
            return pack("C", 0);
        }
    }

    static function write($data)
    {
            self::$global_counter += strlen($data);
            self::$meta_buffer .= $data;
            if(strlen(self::$meta_buffer) >= self::$meta_interval)
            {
                
                $splits = str_split(self::$meta_buffer, self::$meta_interval);
                
                echo $splits[0];
                if(self::$meta_enabled == true) 
                {
                    echo self::writeTag(); 
                }
                $udelay = (1 / (1.01 * config::getSetting("streaming", "lores_default_bitrate") / 8) * strlen($splits[0])) * 1000000;
                self::delay($udelay);
                self::$meta_buffer = isset($splits[1]) ? $splits[1] : "";
            }
    }
    
    private static function delay($duration)
    {
        //if(self::$global_counter > (self::$preload_size * config::getSetting("streaming", "lores_default_bitrate") / 8))
        //{
            usleep($duration);
        //}
    }
    
}
