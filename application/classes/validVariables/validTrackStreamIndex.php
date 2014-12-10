<?php

class validTrackStreamIndex extends validVariable
{
    public function __construct($value)
    {
        if ((int) $value < 1)
        {
            throw new validException("Invalid value for track stream index");
        }
        
        $this->variable = (int) $value;
    }
}
