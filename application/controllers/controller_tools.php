<?php

class get_controller extends controller {
    public function m3u() {
        try {
            $stream = application::singular("stream", application::get("stream_id", null, REQ_STRING));
        } catch (Exception $ex) {
            throw new patDocumentNotFoundException($ex);
        }

        $tmpl = new template("application/tmpl/playlist.tmpl");
        $tmpl
            ->addVariable("stream_name", $stream->getStreamName())
            ->addVariable("stream_id", $stream->getStreamId());

        header("Content-Type: application/octet-stream; charset=utf-8");
        header(sprintf(
            "Content-Disposition: attachment; filename=\"%s (#%d) @ myownradio.biz.m3u\"",
            $stream->getStreamName(),
            $stream->getStreamId()
        ));
        echo $tmpl->makeDocument();
    }

    public function templates() {
        $data = array();
        $templates = new File("application/tmpl");

        foreach ($templates->getDirContents() as $file) {
            $key = $file->filename();
            $content = $file->getContents();
            $data[$key] = $content;
        }

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($data);
    }
}
