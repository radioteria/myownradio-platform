use crate::gstreamer_utils::make_element;
use crate::pipeline::{make_aac_encoder, make_h264_encoder};
use gstreamer::prelude::*;
use gstreamer::{Bin, Element, PadProbeData, PadProbeReturn, PadProbeType, State};
use tracing::info;

#[derive(Debug, thiserror::Error)]
pub(crate) enum StreamError {}

pub(crate) enum StreamOutput {
    RTMP { url: String, stream_key: String },
}

pub(crate) struct StreamConfig {
    pub(crate) output: StreamOutput,
}

pub(crate) fn create_stream(webpage_url: String, config: &StreamConfig) -> Result<(), StreamError> {
    let pipeline = gstreamer::Pipeline::new(Some("test"));

    let audiomixer = make_element("audiomixer");

    let cefbin = make_element("cefbin");
    let cefsrc = cefbin
        .downcast_ref::<Bin>()
        .unwrap()
        .by_name("cefsrc")
        .unwrap();
    cefsrc.set_property("url", webpage_url);

    pipeline
        .add_many(&[&cefbin, &audiomixer])
        .expect("Unable to add elements to pipeline");

    cefbin.link(&audiomixer).unwrap();

    let (video_sink, video_src) = make_h264_encoder(&pipeline);
    let (audio_sink, audio_src) = make_aac_encoder(&pipeline);

    Element::link_many(&[&cefbin, &video_sink]).expect("Unable to link elements");
    Element::link_many(&[&audiomixer, &audio_sink]).expect("Unable to link elements");

    let clocksync = make_element("clocksync");
    let flvmux = make_element("flvmux");
    let rtmp2sink = make_element("rtmp2sink");
    flvmux.set_property("streamable", &true);
    flvmux.set_property("latency", &1_000_000_000_u64);

    if let StreamOutput::RTMP { url, stream_key } = &config.output {
        rtmp2sink.set_property("location", format!("{}/{}", url, stream_key));
    }

    pipeline
        .add_many(&[&clocksync, &flvmux, &rtmp2sink])
        .expect("Unable to add clocksync or flvmux to pipeline");

    Element::link_many(&[&flvmux, &clocksync, &rtmp2sink]).unwrap();

    let flv_video_sink = flvmux
        .request_pad_simple("video")
        .expect("Unable to get flv video");
    let flv_audio_sink = flvmux
        .request_pad_simple("audio")
        .expect("Unable to get flv video");

    video_src
        .static_pad("src")
        .expect("Unable to get video src")
        .link(&flv_video_sink)
        .unwrap();
    audio_src
        .static_pad("src")
        .expect("Unable to get audio src")
        .link(&flv_audio_sink)
        .unwrap();

    clocksync
        .static_pad("src")
        .expect("Unable to get pad")
        .add_probe(PadProbeType::BUFFER, |_pad, info| {
            if let Some(PadProbeData::Buffer(buffer)) = &info.data {
                info!("Buffer {:?}", buffer.pts());
            }

            PadProbeReturn::Pass
        })
        .expect("Unable to add probe");

    pipeline
        .set_state(State::Playing)
        .expect("Unable to start pipeline");

    Ok(())
}
