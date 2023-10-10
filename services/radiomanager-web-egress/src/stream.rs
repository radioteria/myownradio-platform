use gstreamer::prelude::GstBinExtManual;
use gstreamer::Element;

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
    let videotestsrc = gstreamer::ElementFactory::make("videotestsrc")
        .build()
        .expect("Unable to make videotestsrc");
    let fakevideosink = gstreamer::ElementFactory::make("fakevideosink")
        .build()
        .expect("Unable to make fakevideosink");

    pipeline
        .add_many(&[&videotestsrc, &fakevideosink])
        .expect("Unable to add elements to pipeline");

    Element::link_many(&[&videotestsrc, &fakevideosink]).expect("Unable to link elements");

    Ok(())
}
