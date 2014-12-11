<?php

class post_controller extends authController {

    public function createStream()
    {
        $streamName      = new validStreamName(application::post("name", "", REQ_STRING));
        $streamInfo      = new validStreamDescription(application::post("description", "", REQ_STRING));
        $streamGenres    = new ArrayObject(application::post("genre", array("test")));
        $streamPermalink = new validPermalink(application::post("permalink", "", REQ_STRING));
        $streamCategory  = new validCategory(application::post("category", 13, REQ_INT));
        
        $creator = new Fabric();
        
        echo $creator->createStream($streamName, $streamInfo, $streamGenres, $streamPermalink, $streamCategory);
    }
    
    public function modifyStream() {
        $streamInstance  = new radioStream(application::post("id", "", REQ_STRING), true);

        $streamName      = new validStreamName(application::post("name", "", REQ_STRING));
        $streamInfo      = new validStreamDescription(application::post("description", "", REQ_STRING));
        $streamGenres    = new String(application::post("genres", "", REQ_STRING));
        $streamPermalink = new validPermalink(application::post("permalink", "", REQ_STRING));
        $streamCategory  = new validCategory(application::post("category", 13, REQ_INT));
        
        echo $streamInstance->modify($streamName, $streamInfo, $streamGenres, 
                $streamPermalink, $streamCategory);
    }
    
    public function setState() {
        $streamInstance  = new radioStream(application::post("id", "", REQ_STRING), true);
        $newState        = new validStreamState(application::post("state", 0, REQ_INT));
        
        echo $streamInstance->setState($newState);
    }

    public function switchState() {
        $streamInstance  = new radioStream(application::post("id", "", REQ_STRING), true);
        
        echo $streamInstance->setState(new validStreamState(1 - $streamInstance->getDetails()->getState()));
    }

        public function getStream() {
        $stream = new radioStream(application::post("id", "", REQ_STRING));
        
        echo misc::okJSON($stream->toArray());
    }
    
    public function deleteStream() {
        $stream = new radioStream(application::post("id", "", REQ_STRING), true);
        
        echo $stream->delete();
    }
    
    public function purgeStream() {
        $stream = new radioStream(application::post("id", "", REQ_STRING), true);
        
        echo $stream->purge();
    }
    
    public function shuffleStream() {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        $streamTrackList    = $stream->getTrackList();
        
        echo $streamTrackList->shuffle();
    }
    
    public function removePicture() {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        
        echo $stream->deletePicture();
    }

    public function changePicture() {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        $file               = @$_FILES['file'];

        if($file === null) {
            throw new morException("No image file specified");
        }

        echo $stream->changePicture($file);
    }

    // Operations with tracklist
    public function removeTracks()
    {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        $tracks             = new validUniqueList(application::post("tracks", "", REQ_STRING));
        
        echo $stream->getTrackList()->removeTracks($tracks);
    }
    
    public function addTracks()
    {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        $tracks             = new validTrackList(application::post("tracks", "", REQ_STRING));
        
        echo $stream->getTrackList()->addTracks($tracks);
    }
    
    public function moveTrack()
    {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        $uniqueId           = new validUniqueId(application::post("unique_id", null, REQ_STRING));
        $newIndex           = new validTrackStreamIndex(application::post("new_index", 1, REQ_INT));
        
        echo $stream->getTrackList()->moveTrack($uniqueId, $newIndex);
    }
    
    public function playFrom()
    {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);
        $uniqueId           = new validUniqueId(application::post("unique_id", null, REQ_STRING));
        $startFrom          = application::post("start", 0, REQ_INT);
        
        $stream->getTrackList()->setCurrent($uniqueId->get(), $startFrom, true);
        
        echo misc::okJSON();
    }
    
    public function optimize()
    {
        $stream             = new radioStream(application::post("id", "", REQ_STRING), true);

        $stream->getTrackList()->optimizeTrackList();
        
        echo misc::okJSON();
    }
}
