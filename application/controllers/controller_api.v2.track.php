<?php

class post_controller extends authController {

    public function getInfo() {

        $track = new radioTrackItem(application::getPostOptional("id")->getOrElseThrow(new morException("Incorrect parameters")));

        echo json_encode($track->getDetails()->toArray());
    }

    public function putInfo() {

        $track = new radioTrackItem(
            application::getPostOptional("id")->getOrElseThrow(new morException("Incorrect parameters")), true);

        $metadata = InputValidator::trackMetadataValidator(
            application::getPostOptional("metadata")->getOrElseNull());

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
        $optional = Optional::ofNull(@$_FILES["file"]);
        echo $fabric->uploadTrack($optional->getOrElseThrow(new trackException("No file attached")));

    }

    public function test() {
        $function = function ($arg1, $arg2) {
            echo 'Hello, World!';
        };
        $reflection = new ReflectionFunction($function);
        print_r($reflection->getParameters());
    }
}