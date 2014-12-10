<?php

class trackqueue
{
    private $value = null;
    private $next = null;
    private $result = null;
    
    function __construct($tracks)
    {
        
        $data = explode(",", $tracks, 2);
        
        if(strlen($data[0]) === 0)
        {
            throw new Exception("Queue length has zero size", 0);
        }
        
        if(count($data) === 2)
        {
            $this->value = $data[0];
            try
            {
                $this->next = new trackqueue($data[1]);
            }
            catch(Exception $e) 
            {
                $this->next = null;
            }
        }
        elseif(count($data) === 1)
        {
            $this->value = $data[0];
        }
    }
    
    function iterator($callback)
    {
        $i = 0;
        for($temp = $this; $temp != null; $temp = $temp->next())
        {
            $temp->result = call_user_func($callback, $temp->value(), $i ++);
        }
    }
    
    public function toArray()
    {
        return $this->next() != null ? array_merge($this->mkArray(), $this->next()->toArray()) : $this->mkArray();
    }
    
    public function __toString()
    {
        $list = "";
        for($temp = $this; $temp != null; $temp = $temp->next())
        {
            $list .= $temp->value() . ($temp->next() ? "," : "");
        }
        return $list;
    }
    
    private function mkArray()
    {
        return array(array(
            'value' => $this->value,
            'result' => $this->result
        ));
    }

    function length()
    {
        return $this->next() != null ? 1 + $this->next()->length() : 1;
    }
    
    function result()
    {
        return $this->result;
    }
    
    function value()
    {
        return $this->value;
    }
    
    function next()
    {
        return $this->next;
    }
}
