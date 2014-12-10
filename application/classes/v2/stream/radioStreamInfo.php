<?php

/**
 * radioStreamInfo used to implement methods for accessing stream information
 *
 * @author Roman
 */
class radioStreamInfo
{
    protected $streamInformation;
    private $required_keys = array(
        'sid', 'uid', 'name', 'permalink', 'info', 'status',
        'started', 'started_from', 'access', 'category', 'hashtags'
    );
    
    public function __construct(array $stream)
    {
        foreach($this->required_keys as $key)
        {
            if (array_key_exists($key, $stream) === false)
            {
                throw new streamException("Stream object could not be created. Insufficient keys.", 1001);
            }
        }
        $this->streamInformation = $stream;
        /*$this->streamInformation['cover_url'] = is_null($this->streamInformation['cover']) ? null :
            Folders::getStreamCoversUrlPath() . "/" . $this->streamInformation['cover'];*/
    }
    
    public function getId()
    {
        return (int) $this->streamInformation['sid'];
    }
    
    public function getName()
    {
        return new String($this->streamInformation['name']);
    }
    
    public function getPermalink()
    {
        return $this->streamInformation['permalink'];
    }
    
    public function getOwner()
    {
        return (int) $this->streamInformation['uid'];
    }
    
    public function getState()
    {
        return (int) $this->streamInformation['status'];
    }
    
    public function getInfo()
    {
        return new String($this->streamInformation['info']);
    }
    
    public function getLink()
    {
        return "/stream/" . (strlen($this->streamInformation['permalink']) > 0 
                ? $this->streamInformation['permalink'] 
                : $this->getStreamId()
            );
    }
    
    public function toArray()
    {
        return $this->streamInformation;
    }
    
    public function getCoverFile() {
        return new File(Folders::genStreamCoverPath($this->streamInformation['cover']));
    }

    public function getCoverUrlPath() {
        return Folders::genStreamCoverPath($this->streamInformation['cover']);
    }

}
