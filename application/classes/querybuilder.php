<?php

class querybuilder
{
    private $action = null;
    private $from = array();
    private $sets = array();
    private $wheres = array();
    private $orders = array();
    private $limit = null;
    private $selects = array();
    private $into = null;
    private $update = null;
    
    function __construct($action)
    {
        $this->action = $action;
    }
    
    function addSelect($sel)
    {
        $this->selects[] = $sel;
        return $this;
    }
    
    function setUpdate($dst)
    {
        $this->update = $dst;
        return $this;
    }
    
    function addSet($key)
    {
        $this->sets[] = $key;
        return $this;
    }
    
    function setFrom($dst)
    {
        $this->from = array($dst);
        return $this;
    }
    
    function addFrom($dst)
    {
        $this->from[] = $dst;
        return $this;
    }
    
    function setInto($dst)
    {
        $this->into = $dst;
        return $this;
    }
    
    function addWhere($key)
    {
        $this->wheres[] = $key;
        return $this;
    }
    
    function addOrder($key)
    {
        $this->orders[] = $key;
        return $this;
    }
    
    function setLimit($from, $limit)
    {
        $this->limit = array('from' => $from, 'limit' => $limit);
        return $this;
    }
    
    function build()
    {
        $buffer = "";
        $buffer .= $this->action;
        if(count($this->selects) > 0)
        {
            $buffer .= " " . implode(",", $this->selects);
        }
        if($this->update)
        {
            $buffer .= " " . $this->update;
        }
        if($this->from)
        {
            $buffer .= " FROM " . implode(",", $this->from);
        }
        if($this->into)
        {
            $buffer .= " INTO " . $this->into;
        }
        if(count($this->sets) > 0)
        {
            $buffer .= " SET " . implode(",", $this->sets);
        }
        if(count($this->wheres) > 0)
        {
            $buffer .= " WHERE " . implode(" AND ", $this->wheres);
        }
        if(count($this->orders) > 0)
        {
            $buffer .= " ORDER BY " . implode(",", $this->orders);
        }
        if($this->limit)
        {
            $buffer .= " LIMIT " . $this->limit['from'] . "," . $this->limit['limit'];
        }
        return $buffer;
    }
    
    function __toString()
    {
        return $this->build();
    }
}
