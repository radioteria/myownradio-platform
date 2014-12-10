<?php

class validMetadata extends validVariable
{
    public function __construct($data)
    {
        if (!is_array($data))
        {
            throw new validException("Wrong metadata");
        }
        
        $reqKeys = array("artist", "title", "album", "track_number", "genre", "date");
        
        foreach($reqKeys as $key)
        {
            if (array_key_exists($key, $data) === false)
            {
                throw new validException("Wrong metadata");
            }
        }

        $this->variable = $data;
    }
}
