<?php

class radioTrackWorks {
    public static function remove(validTrackList $tracks) {
        self::truncate($tracks);
        self::delete($tracks);
    }
    
    public static function truncate(validTrackList $tracks) {
        $database   = Database::getInstance();
        
        $result = $database->query("SELECT `stream_id`, GROUP_CONCAT(`unique_id`) as `unique_ids` FROM `r_link` WHERE FIND_IN_SET(`track_id`, ?) GROUP BY `stream_id`", 
                array($tracks->get()));
        
        foreach($result as $case) {
            try {
                $list       = new validUniqueList($case['unique_ids']);
                $stream     = new radioStream($case['stream_id'], true);
                $stream->getTrackList()->removeTracks($list);
            } catch (Exception $ex) {
                misc::writeDebug("Exception: " . $ex->getMessage());
            } finally {
                unset($stream);
                unset($list);
            }
        }
    }
    
    public static function delete(validTrackList $tracks) {
        foreach($tracks->getArray() as $track) {
            try {
                $item = new radioTrackItem($track, true);
                $item->delete();
            } catch (Exception $ex) {

            }
        }
    }

}
