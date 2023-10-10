use crate::gstreamer_utils::{make_capsfilter, make_element};
use gstreamer::prelude::*;
use gstreamer::{Caps, Element, Pipeline};

pub(crate) fn make_h264_encoder(pipeline: &Pipeline) -> (Element, Element) {
    let queue_in = make_element("queue");
    let videoconvert = make_element("videoconvert");
    let x264enc = make_element("x264enc");
    x264enc.set_property("key-int-max", 60u32);
    x264enc.set_property("bitrate", 2_500u32);
    let h264parse = make_element("h264parse");
    let caps = make_capsfilter(
        &Caps::builder("video/x-h264")
            .field("profile", "baseline")
            .field("width", 1280)
            .field("height", 720)
            .field("rate", 30)
            .build(),
    );
    let queue_out = make_element("queue");

    pipeline
        .add_many(&[
            &queue_in,
            &videoconvert,
            &x264enc,
            &h264parse,
            &caps,
            &queue_out,
        ])
        .expect("Unable to add elements to pipeline");

    Element::link_many(&[
        &queue_in,
        &videoconvert,
        &x264enc,
        &h264parse,
        &caps,
        &queue_out,
    ])
    .expect("Unable to link elements");

    (queue_in, queue_out)
}

pub(crate) fn make_aac_encoder(pipeline: &Pipeline) -> (Element, Element) {
    let queue_in = make_element("queue");
    let audioconvert = make_element("audioconvert");
    let fdkaacenc = make_element("fdkaacenc");
    let aacparse = make_element("aacparse");
    let caps = make_capsfilter(&Caps::builder("audio/mpeg").field("rate", 44100).build());
    let queue_out = make_element("queue");

    pipeline
        .add_many(&[
            &queue_in,
            &audioconvert,
            &fdkaacenc,
            &aacparse,
            &caps,
            &queue_out,
        ])
        .expect("Unable to add elements to pipeline");

    Element::link_many(&[
        &queue_in,
        &audioconvert,
        &fdkaacenc,
        &aacparse,
        &caps,
        &queue_out,
    ])
    .expect("Unable to link elements");

    (queue_in, queue_out)
}
