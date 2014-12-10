<?php

class get_controller extends controller
{
    public function getList()
    {
        header("Content-Type: application/json");

        $from = application::get("from", 0, REQ_INT);
        $limit = application::get("limit", 50, REQ_INT);

        $streams = Streams::getStreamList($from, $limit);

        echo json_encode($streams);
    }

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

    public function getFilteredStreamList() {
        header("Content-Type: application/json");

        $filter = application::get("q", "*", REQ_STRING);
        $from = application::get("from", 0, REQ_INT);
        $limit = application::get("limit", 50, REQ_INT);

        $streams = Streams::getStreamListFiltered($filter, $from, $limit);

        echo json_encode($streams);
    }
}

