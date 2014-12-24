<?php

class get_controller extends controller {

    public function getOne() {
        header("Content-Type: application/json");

        $id = application::get("id", "", REQ_STRING);

        echo json_encode(Streams::getOneStream($id));
    }

    public function getSimilarTo() {
        header("Content-Type: application/json");

        $id = application::get("id", "", REQ_STRING);

        echo json_encode(Streams::getSimilarTo($id));
    }

    public function getList() {

        header("Content-Type: application/json");

        $filter = application::getParamOptional("q")->getOrElseNull();
        $category = application::getParamOptional("c")->getOrElseNull();

        $from = (int)application::getParamOptional("from")->getOrElse(0);
        $limit = (int)application::getParamOptional("limit")->getOrElse(50);

        $streams = Streams::getStreamListFiltered($filter, $category, $from, $limit);


        echo json_encode($streams);

    }
}

