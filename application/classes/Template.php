<?php

class Template
{
    protected $template;
    protected $variables;
    protected $raw;
    
    public function __construct($template)
    {
        $file = new File($template);
        if (!$file->exists())
        {
            throw new Exception("Template not found", null, null);
        }
        $this->reset()->template = $file->getContents();
    }
    
    public function addVariable($key, $value, $raw = false)
    {
        $this->variables->{$key} = $value;
        $this->raw->{$key} = $raw;
        return $this;
    }
    
    public function makeDocument()
    {
        $result = preg_replace_callback("/\\$\{(.+?)\}/", function ($match) {
            if (isset($this->variables->{$match[1]}))
            {
                if ($this->raw->{$match[1]} === false)
                {
                    return htmlspecialchars($this->variables->{$match[1]});
                }
                else
                {
                    return $this->variables->{$match[1]};
                }
            }
            else
            {
                return "";
            }
        }, $this->template);
        
        return $result;
    }
    
    public function reset()
    {
        $this->variables = new stdClass();
        $this->raw = new stdClass();
        return $this;
    }
}
