<?php

class get_controller extends controller {

    public static function getStreamCover() {

        $filename = new String(application::get("fn", null, REQ_STRING));

        if ($filename === null || $filename->length === 0) {
            header("HTTP/1.1 404 Not Found");
            exit("HTTP/1.1 404 Not Found");
        }

        $cover_size = application::get("size", null, REQ_INT);

        if (is_numeric($cover_size) && ($cover_size <= 0 || $cover_size > 2560)) {
            header("HTTP/1.1 406 Not Acceptable");
            exit("HTTP/1.1 406 Not Acceptable");
        }

        $stream_cover = new File(Folders::genStreamCoverPath($filename));

        if ($stream_cover === null || !$stream_cover->exists()) {
            header("HTTP/1.1 404 Not Found");
            exit("HTTP/1.1 404 Not Found");
        }

        if (in_array($stream_cover->extension(), array('jpg', 'png', 'gif')) === false) {
            header("HTTP/1.1 406 Not Acceptable");
            exit("HTTP/1.1 406 Not Acceptable");
        }

        header("Content-Type: " . $stream_cover->getContentType());
        header(sprintf('Content-Disposition: filename="%s"', $stream_cover->filename()));

        if ($cover_size === null) {
            $stream_cover->echoContents();
        } else {
            $image = new acResizeImage($stream_cover->path());
            $image->cropSquare();
            $image->resize($cover_size);
            $image->interlace();
            $image->output($stream_cover->extension(), 50);
        }

    }

}