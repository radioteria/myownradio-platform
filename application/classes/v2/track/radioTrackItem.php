<?php

class radioTrackItem extends Model {
    protected $object, $track_id;
    
    public function __construct($id, $write = false) {
        parent::__construct();
        $this->track_id = $id;
        $this->reload();
        
        $client = Visitor::getInstance();
        
        if (($write === true) && ($this->getDetails()->getOwner() !== $client->getId())) {
            throw new patNoPermissionException();
        }
    }
    
    public function reload() {
        $trackObject = $this->database->query_single_row("SELECT * FROM `r_tracks` WHERE `tid` = ?", array($this->track_id));
        
        if ($trackObject === null) {
            throw new trackException("Track not found", 4002, null);
        }
        
        $this->object = $trackObject;
        
        return $this;
    }

    public function getDetails() {
        return new radioTrackItemInfo($this->object, $this);
    }
    
    public function updateMetadata(validMetadata $metadata) {
        $meta = $metadata->get();
        
        $query = new querybuilder("UPDATE");
        $query->setUpdate("`r_tracks`");
        $query->addSet("`artist` = ?");
        $query->addSet("`title` = ?");
        $query->addSet("`album` = ?");
        $query->addSet("`track_number` = ?");
        $query->addSet("`genre` = ?");
        $query->addSet("`date` = ?");
        $query->addWhere("`tid` = ?");
        $this->database->query_update($query->build(), array(
            $meta['artist'], $meta['title'], $meta['album'], 
            $meta['track_number'], $meta['genre'], $meta['date'],
            $this->track_id
        ));
        return misc::dataJSON($this->reload()->getDetails()->toArray());
    }
    
    public function truncate() {

        $result = $this->database->query("SELECT `stream_id`, GROUP_CONCAT(`unique_id`) as `unique_ids` FROM `r_link` WHERE `track_id` = ? GROUP BY `stream_id`", 
                array($this->track_id));
        
        foreach($result as $case) {
            try {
                $list = new validUniqueList($case['unique_ids']);
                $stream = new radioStream($case['stream_id']);
                $stream->getTrackList()->removeTracks($list);
            } catch (Exception $ex) {
                misc::writeDebug($ex->getTraceAsString());
            } finally {
                unset($stream);
                unset($list);
            }
        }
        
        return misc::okJSON();
    }
    
    public function delete() {

        try {
            $file = $this->getDetails()->originalFile();
            $file->delete();
        } catch (patFileNotFoundException $ex) {
            misc::writeDebug("Exception: " . $ex->getMessage());
        }

        $this->database->query_update("DELETE FROM `r_tracks` WHERE `tid` = ?", array($this->track_id));

    }
 
}
