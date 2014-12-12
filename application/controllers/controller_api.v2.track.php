<?php

class post_controller extends authController {

    public function getInfo() {
        $track = new radioTrackItem(application::post("id", null, REQ_INT));
        echo json_encode($track->getDetails()->toArray());
    }

    public function putInfo() {
        $track = new radioTrackItem(application::post("id", null, REQ_INT), true);
        $metadata = new validMetadata(application::post("metadata", null));
        echo $track->updateMetadata($metadata);
    }

    public function truncate() {
        $trackIds = new validTrackList(application::post("tracks", null, REQ_STRING));
        radioTrackWorks::truncate($trackIds);
        echo misc::okJSON();
    }

    public function delete() {
        $trackIds = new validTrackList(application::post("tracks", null, REQ_STRING));
        radioTrackWorks::remove($trackIds);
        echo misc::okJSON();
    }

    public function upload() {
        $fabric = new Fabric();
        $optional = Optional::ofNull($_FILES["file"]);
        echo $fabric->uploadTrack($optional->getOrElseThrow(new trackException("No file to upload!")));
    }
}