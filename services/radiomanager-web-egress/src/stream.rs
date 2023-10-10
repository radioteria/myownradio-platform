use crate::gstreamer_utils::make_element;
use gstreamer::prelude::*;
use gstreamer::{Bus, Element, PadProbeData, PadProbeReturn, PadProbeType, State};
use tracing::info;

#[derive(Debug, thiserror::Error)]
pub(crate) enum StreamError {}

pub(crate) enum StreamOutput {
    RTMP { url: String, stream_key: String },
}

pub(crate) struct StreamConfig {
    pub(crate) output: StreamOutput,
}

pub(crate) fn create_stream(webpage_url: &str, config: &StreamConfig) -> Result<(), StreamError> {
    let pipeline = gstreamer::Pipeline::new(Some("test"));

    let videotestsrc = make_element("videotestsrc");
    let fakevideosink = make_element("fakevideosink");

    pipeline
        .add_many(&[&videotestsrc, &fakevideosink])
        .expect("Unable to add elements to pipeline");

    Element::link_many(&[&videotestsrc, &fakevideosink]).expect("Unable to link elements");

    fakevideosink
        .static_pad("sink")
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
