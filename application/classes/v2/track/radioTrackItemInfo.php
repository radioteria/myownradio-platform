<?php

class radioTrackItemInfo {
    private $required_fields = array('tid');
    protected $object, $parent;
    
    public function __construct(array $trackData, $parent = null) {
        foreach($this->required_fields as $field) {
            if(array_key_exists($field, $trackData) === false) {
                throw new trackException("Track object could not be created. Insufficient parameters.", 4001);
            }
        }
        $this->object = $trackData;
        $this->parent = $parent;
    }
    
    public function getId() {
        return (int) $this->object['tid'];
    }
    
    public function getOwner() {
        return (int) $this->object['uid'];
    }
    
    public function getFileName() {
        return $this->object['filename'];
    }
    
    public function getExtension() {
        return $this->object['ext'];
    }
    
    public function getArtist() {
        return $this->object['artist'];
    }
    
    public function getTitle() {
        return $this->object['title'];
    }
    
    public function getAlbum() {
        return $this->object['album'];
    }
    
    public function getTrackNumber() {
        return $this->object['track_number'];
    }
    
    public function getGenre() {
        return $this->object['genre'];
    }
    
    public function getDate() {
        return $this->object['date'];
    }
    
    public function getDuration() {
        return (int) $this->object['duration'];
    }
    
    public function getSize() {
        return (int) $this->object['filesize'];
    }
    
    public function getColor() {
        return (int) $this->object['color'];
    }
    
    public function isBlocked() {
        return (bool) $this->object['blocked'];
    }
    
    public function getUploadDate() {
        return (int) $this->object['uploaded'];
    }
    
    public function getCaption() {
        if ((strlen($this->object['artist']) > 0) && (strlen($this->object['title']) > 0)) {
            return $this->object['artist'] . " - " . $this->object['title'];
        } else if (strlen($this->object['title']) > 0) {
            return $this->object['title'];
        } else {
            return $this->object['filename'];
        }
    }
    
    public function toArray() {
        return $this->object;
    }
    
    public function originalFile() {
        return new File(sprintf("%s/ui_%d/a_%03d_original.%s", 
            config::getSetting("content", "content_folder"), 
            $this->getOwner(), 
            $this->getId(), 
            $this->getExtension()
        ));
    }
           
}
