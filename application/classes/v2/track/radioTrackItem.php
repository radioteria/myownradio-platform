<?php

class radioTrackItem extends Model {
    protected $object, $track_id;
    
    public function __construct($id, $write = false) {
        parent::__construct();
        $this->track_id = $id;
        $this->reload();
        
        $client = User::getInstance();
        
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
    
    public function updateMetadata(array $metadata) {

        $fluent = Database::getFluentPDO();

        $query = $fluent->update("r_tracks");
        $query->set([
            "artist"        => $metadata["artist"],
            "title"         => $metadata["title"],
            "album"         => $metadata["album"],
            "track_number"  => $metadata["track_number"],
            "genre"         => $metadata["genre"],
            "date"          => $metadata["date"]
        ]);
        $query->where("tid", $this->track_id);

        $this->database->executeUpdate($query->getQuery(false), $query->getParameters());

        return misc::dataJSON($this->reload()->getDetails()->toArray());

    }
    
    public function truncate() {

        $query = $this->database->getFluentPDO()->from("r_link")
            ->select(null)
            ->select([
                "stream_id",
                "GROUP_CONCAT(unique_id) as unique_ids"])
            ->where("track_id", $this->track_id)
            ->groupBy("stream_id");

        $result = $this->database->fetchAll($query->getQuery(false), $query->getParameters());
        
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
            $file = $this->getDetails()->getOriginalFile();
            $file->delete();
        } catch (patFileNotFoundException $ex) {
            misc::writeDebug("Exception: " . $ex->getMessage());
        }

        $this->database->executeUpdate("DELETE FROM r_tracks WHERE tid = ?", array($this->track_id));

    }
 
}
