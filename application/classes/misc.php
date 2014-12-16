<?php

class misc
{
    public static $test = 0;

    
    static function get_audio_echoprint($filename)
    {
        //setlocale(LC_ALL, "en_US.UTF-8");
        $fn_quote = escapeshellarg($filename);
        $fetch_cmd = ApplicationConfig::getSetting('getters', 'echoprint') . " " . $fn_quote . " 0 30";
        //misc::writeDebug($fetch_cmd);
        exec($fetch_cmd, $data, $exit);
        $data = implode("", $data);
        try 
        {
            $json = json_decode($data, true);
            return isset($json[0]['code']) ? $json[0]['code'] : false;
        } 
        catch (Exception $ex) 
        {
            return false;
        }
    }

    static function findMp3Header($data, $starting = 0)
    {
        if (strlen($data) < 4)
        {
            return false;
        }

        for ($n = $starting; $n < strlen($data) - 3; $n ++ )
        {
            if (self::readMp3Header(substr($data, $n, 4)) !== false)
            {
                return $n;
            }
        }
        return false;
    }

    
    static function readMp3Header($header)
    {

        if (strlen($header) < 4)
        {
            return false;
        }

        // Convert header string to bits
        $header_bits = unpack("N", $header);

        // Check bits correctness
        if (($header_bits[1] & 0xFFE << 20) != 0xFFE << 20)
        {
            return false;
        }

        // Seems to be ok. Trying to decode
        $mp3_header = array();

        $version_array = array('MPEG Version 2.5 (not an official standard)',
            null, 'MPEG Version 2', 'MPEG Version 1');

        $header_array = array('Unknown', 'Layer III', 'Layer II', 'Layer I');

        $bitrate_array = array(null, 32, 40, 48, 56, 64, 80, 96, 112,
            128, 160, 192, 224, 256, 320, null);

        $sampling_array = array(44100, 48000, 32000, "Unknown");
        $channels_array = array("Stereo", "Joint Stereo", "Dual", "Mono");
        $emphasis_array = array("None", "50/15", null, "CCIT J.17");

        if (($header_bits[1] & 0xF << 12) >> 12 == 0xF)
        {
            return false;
        }

        $mp3_header['version'] = $version_array[($header_bits[1] & 0x3 << 19) >> 19];
        $mp3_header['layer'] = $header_array[($header_bits[1] & 0x3 << 17) >> 17];
        $mp3_header['crc'] = (($header_bits[1] & 0x1 << 15) >> 15) ? "No" : "True";
        $mp3_header['bitrate'] = $bitrate_array[($header_bits[1] & 0xF << 12) >> 12];
        $mp3_header['samplerate'] = $sampling_array[($header_bits[1] & 0x3 << 10) >> 10];
        $mp3_header['padding'] = (($header_bits[1] & 0x1 << 9) >> 9) ? "Yes" : "No";
        $mp3_header['channels'] = $channels_array[($header_bits[1] & 0x3 << 6) >> 6];
        $mp3_header['emphasis'] = $emphasis_array[$header_bits[1] & 0x3];

        // Skip frame if not Layer III
        if ($mp3_header['layer'] != "Layer III")
        {
            misc::writeDebug("Wrong layer: " . $mp3_header['layer'], 1);
            return false;
        }

        //  Skip if wrong sampling rate
        if ($mp3_header['samplerate'] != 44100)
        {
            return false;
        }

        $mp3_header['framesize'] = floor(144000 * $mp3_header['bitrate'] / $mp3_header['samplerate']);

        if ($mp3_header['framesize'] == 0)
        {
            return false;
        }

        $mp3_header['padding'] == "Yes" ? $mp3_header['framesize'] ++ : null;

        return $mp3_header;
    }

    static function searchQueryFilter($text)
    {
        $query = "";
        $words = preg_split("/(*UTF8)((?![\\p{L}|\\p{N}|\\#])|(\\s))+/", $text);
        
        
        foreach($words as $word)
        {
            if(strlen($word) > 0)
            {
                $query .= "+{$word} ";
            }
        }
        $query .= "*";
        return $query;
    }
    
    static function generateId()
    {
        $id_length = 8;
        $id_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $id = "";
        for ($i = 0; $i < $id_length; $i ++ )
        {
            $id .= substr($id_chars, rand(0, strlen($id_chars) - 1), 1);
        }
        return $id;
    }

    static function convertuSecondsToTime($useconds)
    {
        $seconds = $useconds / 1000;
        return sprintf("%01d:%02d:%02d", floor($seconds / 3600), floor($seconds / 60) % 60, $seconds % 60);
    }

    static function trackDuration($useconds)
    {
        $seconds = floor($useconds / 1000);
        return sprintf("%02d:%02d", floor($seconds / 60), $seconds % 60);
    }

    static function writeDebug($message, $code = 0)
    {
/*        $file = fopen("/tmp/myownradio.dev.log", "a");
        fwrite($file, sprintf("%s %s %s\n", date("Y.m.d H:i:s", time()), application::getClient(), htmlspecialchars($message)));
        fclose($file);*/
    }

    static function mySort($array)
    {
        if ( ! is_array($array))
        {
            return false;
        }
        if (count($array) <= 1)
        {
            return $array;
        }
        $f = 0;
        while (true)
        {
            if ($array[$f] <= $array[$f + 1])
            {
                ++ $f;
                if ($f >= count($array) - 1)
                {
                    return $array;
                }
            }
            else
            {
                $temp = $array[$f + 1];
                $array[$f + 1] = $array[$f];
                $array[$f] = $temp;
                if ($f > 0)
                {
                    -- $f;
                }
            }
        }
    }

    static function outputJSON($code, $data = array())
    {
        return json_encode(array(
            'code' => $code,
            'data' => $data
        ));
    }
    
    static function newJSON($res = 0, $code = null, $description = null, $data = null)
    {
        return json_encode(array(
            'result'      => $res,
            'code'        => $code,
            'description' => $description,
            'data'        => $data,
            'stats'       => application::getProfileStats()
        ));
    }
    
    static function okJSON($jobs = array())
    {
        return json_encode(array('status' => 1, 'jobs' => $jobs));
    }
    
    static function errJSON($message = null, $context = null)
    {
        return json_encode(array('status' => 0, 'message' => $message, 'context' => $context));
    }
    
    static function dataJSON($data = null)
    {
        return json_encode(array('status' => 1, 'data' => $data));
    }
    
    static function errorJSON($code)
    {
        exit(json_encode(array(
            'error' => $code
        )));
    }
    

    static function execute($data, $_MODULE = NULL)
    {
        ob_start();
        eval("?>" . $data);
        return ob_get_clean();
    }

    static function executeFile($filename, $_MODULE = NULL)
    {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    

    
}
